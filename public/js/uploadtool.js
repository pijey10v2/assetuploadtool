/**
 * uploadtool.js
 * 
 * Handles all AJAX logic for:
 *  - Uploading files
 *  - Rendering mapping table
 *  - Executing data updates
 *  - Loading asset tables (via API)
 */

$(document).ready(function () {

    /**
     * =============================
     *   INIT SELECT2 FIELDS
     * =============================
     */
    $('#bimfile, #data_id').select2({
        placeholder: 'Search...',
        allowClear: true,
        width: '100%'
    });

    /**
     * =============================
     *   LOAD ASSET TABLES VIA API
     * =============================
     */
    async function loadAssetTables() {
        const dropdown = $('#asset_table_name');
        dropdown.html('<option selected disabled>Loading tables...</option>');

        try {
            const response = await fetch(window.uploadToolConfig.api.getAllTables);
            const data = await response.json();

            console.log('API Response:', data); 

            if (data.status === "success" && Array.isArray(data.tables)) {
                dropdown.html('<option value="" selected disabled>Select a table</option>');

                data.tables.forEach(item => {
                    // Defensive extraction
                    const label = item.label ?? item.table ?? 'Unnamed Table';
                    const value = item.table ?? item.label ?? '';

                    dropdown.append(`<option value="${value}">${label}</option>`);
                });

                // Enhance dropdown with Select2
                if ($.fn.select2) {
                    dropdown.select2({
                        placeholder: 'Search Asset Table...',
                        allowClear: true,
                        width: '100%'
                    });
                }
            } else {
                dropdown.html('<option disabled>Error loading tables</option>');
                console.error("Error:", data.message);
            }
        } catch (error) {
            dropdown.html('<option disabled>Failed to fetch tables</option>');
            console.error("Fetch error:", error);
        }
    }

    // Load tables when the page loads
    loadAssetTables();

    /**
     * =============================
     *   FORM SUBMISSION (UPLOAD)
     * =============================
     */
    $('#uploadForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;

        if (!form.checkValidity()) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            return false;
        }

        const formData = new FormData(form);

        $('#processBtn')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: window.uploadToolConfig.routes.store,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#progress-container').show();
                $('#progress-bar')
                    .removeClass('bg-danger bg-success')
                    .addClass('bg-info progress-bar-striped progress-bar-animated')
                    .css('width', '100%')
                    .text('Processing files, please wait...');
                $('#upload-status').html('');
            },
            success: function (response) {

                // Save these globally for Execute step
                window.rawFilePath = response.rawfile_path;
                window.importBatchNo = $('#import_batch_no').val();
                window.dataId = $('#data_id').val();
                window.assetTableName = $('#asset_table_name').val();
                window.excelColumns = response.raw_columns || [];
                window.bimResults = response.bim_results || [];
                window.createdBy = response.createdBy;
                window.createdByName = response.createdByName;

                $('#progress-bar')
                    .removeClass('bg-info progress-bar-striped progress-bar-animated')
                    .addClass('bg-success')
                    .text('Completed');

                $('#upload-status').html(`
                    <div class="alert alert-success mt-3">
                        ${response.message}<br>
                        BIM Rows Found: <strong>${response.bim_count}</strong>
                    </div>
                `);

                // Normalize recent_mapping array to object
                let recentMapping = {};
                if (Array.isArray(response.recent_mapping)) {
                    response.recent_mapping.forEach(item => {
                        const key = Object.keys(item)[0];
                        const value = item[key];
                        recentMapping[key] = value;
                    });
                } else {
                    recentMapping = response.recent_mapping || {};
                }

                renderMappingTable(response.db_columns, response.raw_columns, recentMapping);
                $('#execute-update').prop('disabled', false);
                $('#processBtn').prop('disabled', false).text('Process Again');
                $(form).removeClass('was-validated');
            },
            error: function (xhr, status, error) {
                const msg = xhr.responseJSON?.message || error;
                $('#progress-bar')
                    .removeClass('bg-info progress-bar-striped progress-bar-animated')
                    .addClass('bg-danger')
                    .text('Error');

                $('#upload-status').html(`
                    <div class="alert alert-danger mt-3">
                        Upload failed: ${msg}
                    </div>
                `);

                $('#processBtn').prop('disabled', false).text('Process');
            }
        });
    });

    /**
     * =============================
     *   MAPPING TABLE RENDER
     * =============================
     */
    function renderMappingTable(dbCols, excelCols, recentMapping = {}) {
        const tbody = $('#mapping-table tbody');
        tbody.empty();

        if (!dbCols.length && !excelCols.length) {
            tbody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">No mapping data available.</td>
                </tr>
            `);
            return;
        }

        dbCols.forEach(dbCol => {
            // Check if column is locked (for c_model_element)
            const isLocked = dbCol === 'c_model_element';
            const defaultValue = isLocked ? 'Element ID' : '';

            // Determine pre-selected value from recent mapping
            const mappedExcelCol = recentMapping[dbCol] || (isLocked ? defaultValue : '');

            // Filter out "Element ID" from other dropdowns
            const availableOptions = isLocked 
                ? excelCols 
                : excelCols.filter(col => col !== 'Element ID');

            // Generate options (keep preselected if it exists)
            const options = availableOptions
                .map(col => `
                    <option value="${col}" ${col === mappedExcelCol ? 'selected' : ''}>${col}</option>
                `)
                .join('');

            // Add table row
            tbody.append(`
                <tr ${isLocked ? 'class="table-light"' : ''}>
                    <td>
                        <input type="text" class="form-control db-col-input"
                            value="${dbCol}" readonly>
                    </td>
                    <td>
                        <select class="form-select excel-column-select"
                                data-dbcol="${dbCol}" ${isLocked ? 'disabled' : ''}>
                            <option value="">-- Select Excel Column --</option>
                            ${options}
                        </select>
                        ${isLocked ? `
                            <input type="hidden" name="locked_mapping_${dbCol}" value="${defaultValue}">
                            <small class="text-muted">Auto-mapped to "${defaultValue}"</small>
                        ` : ''}
                    </td>
                </tr>
            `);
        });

        // Initialize Select2 (reset any existing instances first)
        $('.excel-column-select').each(function () {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });

        $('.excel-column-select').select2({
            placeholder: 'Search Excel Column...',
            allowClear: true,
            width: '100%'
        });
    }

    /**
     * =============================
     *   HELPER: GET SELECTED MAPPINGS
     * =============================
     */
    function getSelectedMappings() {
        const mappings = [];
        $('#mapping-table tbody tr').each(function () {
            const dbCol = $(this).find('.db-col-input').val().trim();
            const excelCol = $(this).find('.excel-column-select').val();
            if (dbCol && excelCol) mappings.push({ [dbCol]: excelCol });
        });
        return mappings;
    }

    /**
     * =============================
     *   EXECUTE DATA UPDATE
     * =============================
     */
    $('#execute-update').on('click', function () {

        const mappings = getSelectedMappings();

        if (!mappings.length) {
            $('#execute-status').html(`
                <div class="alert alert-warning">
                    Please complete your column mappings before executing.
                </div>
            `);
            return;
        }

        const spinner = $('#execute-loading-container');
        const message = $('#execute-loading-message');
        const status = $('#execute-status');
        const button = $(this);

        // Show spinner and message
        spinner.show();
        message.text('Please wait while the data is being inserted/updated...');
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');

        $.ajax({
            url: window.uploadToolConfig.routes.execute,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                mappings: mappings,
                rawfile_path: window.rawFilePath,
                import_batch_no: window.importBatchNo,
                data_id: window.dataId,
                asset_table_name: window.assetTableName,
                bim_results: window.bimResults,
                createdBy: window.createdBy,
                createdByName: window.createdByName,
            },
            success: function (response) {
                // Begin polling job progress
                pollJobStatus(response.job_id, spinner, message, status, button);
            },
            error: function (xhr) {
                spinner.hide();
                button.prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Execute Data Update');
                status.html(`
                    <div class="alert alert-danger mt-3">
                        ${xhr.responseJSON?.message || 'Error starting job.'}
                    </div>
                `);
            }
        });
    });

    // Poll progress (simplified spinner version)
    function pollJobStatus(jobId, spinner, message, status, button) {
        let polling = setInterval(() => {
            $.ajax({
                url: window.uploadToolConfig.routes.progress,
                type: 'GET',
                data: { job_id: jobId },
                success: function (data) {
                    if (data.status === 'processing' || data.status === 'starting') {
                        message.text('Please wait while the data is being inserted/updated...');
                    }

                    if (data.status === 'done') {
                        clearInterval(polling);
                        spinner.hide();
                        message.text('');
                        status.html(`
                            <div class="alert alert-success mt-3 text-center">
                                <strong>Data Successfully Processed and Updated!</strong><br>
                                Inserted: ${data.inserted} / ${data.total} rows
                            </div>
                        `);
                        button.prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Execute Data Update');
                    }

                    if (data.status === 'error') {
                        clearInterval(polling);
                        spinner.hide();
                        message.text('');
                        status.html(`
                            <div class="alert alert-danger mt-3 text-center">
                                ${data.message || 'An error occurred while processing.'}
                            </div>
                        `);
                        button.prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Execute Data Update');
                    }
                },
                error: function () {
                    clearInterval(polling);
                    spinner.hide();
                    status.html(`
                        <div class="alert alert-danger mt-3 text-center">
                            Failed to check job progress.
                        </div>
                    `);
                    button.prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Execute Data Update');
                }
            });
        }, 2000); // Poll every 2 seconds
    }
});
