@extends('layouts.app')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Optional: Make it fit Bootstrap's form style */
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
</style>

@section('content')
<div class="container mt-5">
    <div class="row g-4 align-items-start">
        <!-- Left Column: Upload Form -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm p-4">
                <h4 class="mb-4 text-center">Upload Tool</h4>

                <!-- Progress Bar -->
                <div class="progress mb-3" style="height: 20px; display: none;" id="progress-container">
                    <div id="progress-bar"
                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                        role="progressbar" style="width:0%">0%</div>
                </div>

                <!-- Status Message -->
                <div id="upload-status" class="mb-3"></div>

                <!-- Upload Form -->
                <form id="uploadForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf

                    <!-- BIM File -->
                     
                    <!-- <div class="mb-3">
                        <label for="bimfile" class="form-label">Select BIM File <span class="text-danger">*</span></label>
                        <select class="form-select" id="bimfile" name="bimfile" required>
                            <option value="">-- Select BIM File --</option>
                            @foreach($bimFiles as $file)
                                <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a BIM file.</div>
                    </div> -->

                    <div class="mb-3">
                        <label for="bimfile" class="form-label">Select BIM File <span class="text-danger">*</span></label>
                        <select class="form-select" id="bimfile" name="bimfile" required>
                            <option value="">Select BIM File...</option>
                            @foreach($bimFiles as $file)
                                <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a BIM file.</div>
                    </div>

                    <!-- RAW File -->
                    <div class="mb-3">
                        <label for="rawfile" class="form-label">RAW File <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="rawfile" name="rawfile"
                            accept=".xlsx,.xls,.csv" required>
                        <div class="invalid-feedback">Please select a valid Excel file (.xlsx / .xls / .csv).</div>
                    </div>

                    <!-- Import Batch No -->
                    <div class="mb-3">
                        <label for="import_batch_no" class="form-label">Import Batch No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="import_batch_no" name="import_batch_no"
                            placeholder="Enter import batch number" required>
                        <div class="invalid-feedback">Please enter an import batch number.</div>
                    </div>

                    <!-- Data ID -->
                    <!-- <div class="mb-3">
                        <label for="data_id" class="form-label">Data ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="data_id" name="data_id"
                            placeholder="Enter data ID" required>
                        <div class="invalid-feedback">Please enter a data ID.</div>
                    </div> -->

                    <div class="mb-3">
                        <label for="data_id" class="form-label">Select Data ID (Layer Name) <span class="text-danger">*</span></label>
                        <select class="form-select" id="data_id" name="data_id" required>
                            <option value="">Select Layer...</option>
                            @foreach($layers as $layer)
                                <option value="{{ $layer->Data_ID }}"> Data ID: {{ $layer->Data_ID }} - Layer Name: {{ $layer->Layer_Name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a Data ID.</div>
                    </div>


                    <!-- Mapping Source -->
                    <div class="mb-3">
                        <label for="asset_table_name" class="form-label">Asset Table <span class="text-danger">*</span></label>
                         <select class="form-select" id="asset_table_name" name="asset_table_name" required>
                            <option selected disabled><span class="spinner-border spinner-border-sm"></span> Loading...</option>
                        </select>
                        <div class="invalid-feedback">Please select a mapping type.</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="processBtn">Process</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Mapping Table -->
        <div class="col-lg-8 col-md-7">
           <div class="card mt-4 shadow-sm">
            <div class="card-header fw-bold bg-white">
                <i class="bi bi-diagram-3 text-primary me-2"></i> Column Mapping
            </div>
            <div class="card-body">
                <table id="mapping-table" class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="45%">Database Column</th>
                            <th width="45%">Excel Column</th>
                            <th width="10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No mapping data available.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-end mt-3">
                    <button id="add-row" type="button" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Custom Mapping
                    </button>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    // Handle form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Bootstrap validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            return false;
        }

        const formData = new FormData(form);

        // Disable button & show spinner
        $('#processBtn')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: "{{ route('uploadtool.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#progress-container').show();
                $('#progress-bar')
                    .removeClass('bg-danger bg-success')
                    .addClass('bg-info progress-bar-striped progress-bar-animated')
                    .css('width', '100%')
                    .text('Processing files, please wait...');
                $('#upload-status').html('');
            },
            success: function(response) {

                // store Excel columns globally for future "Add Row" usage
                window.excelColumns = response.raw_columns || []; // store for later use in add-row

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

                // render initial mapping table
                renderMappingTable(response.db_columns, response.raw_columns);

                // Show Execute button
                $('#execute-btn-container').fadeIn(600);

                // Reset upload button text
                $('#processBtn')
                    .prop('disabled', false)
                    .text('Process Again');

                form.reset();
                $(form).removeClass('was-validated');
            },
            error: function(xhr, status, error) {
                let msg = xhr.responseJSON?.message || error;
                $('#progress-bar')
                    .removeClass('bg-info progress-bar-striped progress-bar-animated')
                    .addClass('bg-danger')
                    .text('Error');

                $('#upload-status').html(`
                    <div class="alert alert-danger mt-3">
                        Upload failed: ${msg}
                    </div>
                `);

                $('#processBtn')
                    .prop('disabled', false)
                    .text('Process');
            }
        });
    });

    // Execute Data Update Button Handler
    $('#executeBtn').on('click', function() {
        alert('Executing data update...');
    });

    // Render Mapping Table with searchable dropdowns
    function renderMappingTable(dbCols, excelCols) {
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

        dbCols.forEach((dbCol) => {
            let options = excelCols.map(col => `<option value="${col}">${col}</option>`).join('');

            tbody.append(`
                <tr>
                    <td><input type="text" class="form-control db-col-input" value="${dbCol}" readonly></td>
                    <td>
                        <select class="form-select excel-column-select" data-dbcol="${dbCol}">
                            <option value="">-- Select Excel Column --</option>
                            ${options}
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        initSelect2();
    }

    // Initialize Select2
    function initSelect2() {
        $('.excel-column-select').select2({
            placeholder: 'Search Excel Column...',
            allowClear: true,
            width: '100%'
        });
    }

    // Helper: Get all current DB column names (trimmed)
    function getAllDbCols() {
        const cols = [];
        $('.db-col-input').each(function() {
            cols.push($(this).val().trim().toLowerCase());
        });
        return cols;
    }

    // Add new custom mapping row
    $('#add-row').on('click', function() {
        const tbody = $('#mapping-table tbody');

        // Remove "no data" message if present
        if (tbody.find('tr td[colspan]').length) tbody.empty();

        // Excel column options
        const excelCols = window.excelColumns || [];
        const options = excelCols.map(col => `<option value="${col}">${col}</option>`).join('');

        tbody.append(`
            <tr>
                <td>
                    <input type="text" class="form-control db-col-input new-db-col" placeholder="Enter new database column name">
                    <div class="invalid-feedback small">Column already exists.</div>
                </td>
                <td>
                    <select class="form-select excel-column-select">
                        <option value="">-- Select Excel Column --</option>
                        ${options}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-row">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        initSelect2();
    });

    // Real-time duplicate validation
    $(document).on('input', '.new-db-col', function() {
        const currentInput = $(this);
        const currentValue = currentInput.val().trim().toLowerCase();
        const allCols = getAllDbCols();

        // Count how many times this value appears
        const duplicateCount = allCols.filter(name => name === currentValue).length;

        if (duplicateCount > 1 && currentValue !== '') {
            currentInput.addClass('is-invalid');
            currentInput.attr('title', 'Duplicate column name');
        } else {
            currentInput.removeClass('is-invalid');
            currentInput.removeAttr('title');
        }
    });

    // Remove mapping row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();

        const tbody = $('#mapping-table tbody');
        if (!tbody.children().length) {
            tbody.append(`
                <tr>
                    <td colspan="3" class="text-center text-muted">No mapping data available.</td>
                </tr>
            `);
        }
    });

    // Collect selected mappings (for submission)
    function getSelectedMappings() {
        const mappings = [];

        $('#mapping-table tbody tr').each(function() {
            const dbColInput = $(this).find('.db-col-input');
            const dbCol = dbColInput.val().trim();
            const excelCol = $(this).find('.excel-column-select').val();

            // Skip invalid or duplicate rows
            if (dbCol && excelCol && !dbColInput.hasClass('is-invalid')) {
                mappings.push({ [dbCol]: excelCol });
            }
        });

        return mappings;
    }


});
</script>

<script>
    const API_GET_ALL_TABLES_URL = "<?php echo $_ENV['API_GET_ALL_TABLES_URL']; ?>";
    async function loadAssetTables() {
      const dropdown = document.getElementById("asset_table_name");

      try {
        const response = await fetch(API_GET_ALL_TABLES_URL);
        const data = await response.json();

        if (data.status === "success" && Array.isArray(data.tables)) {
          dropdown.innerHTML = '<option value="" selected disabled>Select a table</option>';
          data.tables.forEach(table => {
            const option = document.createElement("option");
            option.value = table;
            option.textContent = table;
            dropdown.appendChild(option);
          });
        } else {
          dropdown.innerHTML = '<option disabled>Error loading tables</option>';
          console.error("Error:", data.message);
        }
      } catch (error) {
        dropdown.innerHTML = '<option disabled>Failed to fetch tables</option>';
        console.error("Fetch error:", error);
      }
    }

    // Load tables when the page loads
    document.addEventListener("DOMContentLoaded", loadAssetTables);
</script>

<script>
    $(document).ready(function() {
        $('#bimfile').select2({
            placeholder: 'Search BIM File...',
            allowClear: true,
            width: '100%'
        });

        $('#data_id').select2({
            placeholder: 'Search Layer Name...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
