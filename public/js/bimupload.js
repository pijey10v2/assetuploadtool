$(function() {
    const routes = window.uploadToolConfig.routes;
    const csrfToken = window.uploadToolConfig.csrfToken;

    let currentPage = 1;
    let currentSearch = "";

    // Upload Multiple Files
    $('#bimUploadForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const progressBar = $('#progress-bar');
        const container = $('#progress-container');

        $.ajax({
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        container.show();
                        progressBar.css('width', percent + '%').text(percent + '%');
                    }
                });
                return xhr;
            },
            url: routes.storeBim,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {

                $('#bimfile').val(''); // Clear file input field
                const $status = $('#upload-status');
                $status.html(`
                    <div class="alert alert-success fade show">${response.message}</div>
                `);

                // Fade out after 5 seconds
                setTimeout(() => {
                    $status.fadeOut('slow', function () {
                        $(this).empty().show(); // clear message and restore container
                    });
                }, 5000);

                const page = $(this).data("page");
                loadBimFiles(page, currentSearch);

                $('#bim-pagination').show();
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Upload failed.';
                if (xhr.responseJSON?.errors) {
                    msg += '<br>' + Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                $('#upload-status').html(`<div class="alert alert-danger">${msg}</div>`);
            },
            complete: function() {
                progressBar.css('width', '0%').text('0%');
                container.hide();
            }
        });
    });

    // Clear BIM files with confirmation
    $('#clear-bim-files').on('click', function() {
        Swal.fire({
            title: 'Clear all BIM files?',
            text: 'This will permanently remove all uploaded BIM files.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear all'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.clearBim,
                    type: 'POST', // Send as POST to bypass IIS restrictions
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        _method: 'POST', // Spoof DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function(response) {
                        Swal.fire('Cleared!', response.message, 'success');
                        $('#bimfile').val(''); // Clear file input field
                        // No bim file yet
                        $('#bim-file-list').html('<tr><td colspan="4" class="text-center text-muted">No files found.</td></tr>');
                        // Hide pagination
                        $('#bim-pagination').hide();
                        // Reset counter
                        $('#bimfile-counter').text('Showing 0 of 0 BIM Files');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to clear files.', 'error');
                    }
                });
            }
        });
    });

    // Check All functionality
    $(document).on('change', '#check-all-bim', function () {
        const isChecked = $(this).is(':checked');
        $('.bim-checkbox').prop('checked', isChecked);
    });

    // If a single checkbox is unchecked, uncheck "Check All"
    $(document).on('change', '.bim-checkbox', function () {
        if (!$(this).is(':checked')) {
            $('#check-all-bim').prop('checked', false);
        } else {
            // If all are checked, mark "Check All"
            const allChecked = $('.bim-checkbox').length === $('.bim-checkbox:checked').length;
            $('#check-all-bim').prop('checked', allChecked);
        }
    });

    // Enable/disable delete button
    $(document).on('change', '.bim-checkbox', function() {
        const anyChecked = $('.bim-checkbox:checked').length > 0;
        $('#delete-selected-bim').prop('disabled', !anyChecked);
    });

    // Delete selected BIM files
    $('#delete-selected-bim').on('click', function () {
        const selectedFiles = $('.bim-checkbox:checked').map(function () {
            return $(this).data('filename');
        }).get();

        if (selectedFiles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Files Selected',
                text: 'Please select at least one i.BIM file to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${selectedFiles.length} file(s). This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.clearBimFiles,
                    type: 'POST', // Send as POST to bypass IIS restrictions
                    data: {
                        _method: 'POST', // Spoof DELETE
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        files: selectedFiles
                    },
                    beforeSend: function () {
                        $('#delete-selected-bim').prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm"></span> Deleting...');
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Files Deleted',
                            text: response.message || 'Selected BIM files were deleted successfully.',
                            timer: 2500,
                            showConfirmButton: false
                        });
                        $('#delete-selected-bim').prop('disabled', false).html('<i class="bi bi-trash3"></i> Clear Selected Files');
                        $('#check-all-bim').prop('checked', false);

                        const page = $(this).data("page");
                        loadBimFiles(page, currentSearch);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Deleting Files',
                            text: xhr.responseJSON?.message || 'An unexpected error occurred.'
                        });
                        $('#delete-selected-bim').prop('disabled', false).html('<i class="bi bi-trash3"></i> Clear Selected Files');
                    }
                });
            }
        });
    });


    /**
     * =========================
     *  Load Files (AJAX)
     * =========================
     */
    function loadBimFiles(page = 1, search = "") {
        $.ajax({
            url: routes.listBimFiles,
            type: "GET",
            data: { page, search },
            beforeSend: function() {
                $("#bim-file-list").html('<div class="text-muted text-center py-3">Loading files...</div>');
            },
            success: function(response) {
                const { data, pagination } = response;

                const pg = response.pagination || {};
                const items = response.data || [];
                const loaded = Math.min((pg.current_page - 1) * pg.per_page + items.length, pg.total);

                // Update counter
                $('#bimfile-counter').text(`Showing ${loaded} of ${pg.total} BIM Files`);
                
                renderBimFileList(data);
                renderPagination(pagination);
            },
            error: function() {
                $("#bim-file-list").html('<div class="text-danger text-center py-3">Failed to load files.</div>');
            }
        });
    }

    /**
     * =========================
     *  Render File List
     * =========================
     */
    function renderBimFileList(files) {
        const list = $('#bim-file-list');
        const counter = $('#bimfile-counter');
        list.empty();

        if (!files.length) {
            list.html('<tr><td colspan="4" class="text-center text-muted">No files found.</td></tr>');
            counter.text('Showing 0 of 0 BIM Files');
            return;
        }

        files.forEach(file => {
            list.append(`
                <tr>
                    <td class="text-center">
                         <input type="checkbox" class="form-check-input me-2 bim-checkbox" data-filename="${file.name}">
                    </td>
                    <td><i class="bi bi-file-earmark-text text-primary me-2"></i> ${file.name}</td>
                    <td>${file.uploaded_at}</td>
                    <td>${file.uploaded_by}</td>
                </tr>
            `);
        });
    }

    /**
     * =========================
     *  Render Pagination
     * =========================
     */
    function renderPagination({ total, current_page, last_page }) {
        const container = $("#bim-pagination");
        container.empty();

        if (last_page <= 1) return;

        let paginationHTML = '<ul class="pagination justify-content-center">';
        for (let i = 1; i <= last_page; i++) {
            paginationHTML += `
                <li class="page-item ${i === current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        paginationHTML += '</ul>';

        container.html(paginationHTML);
    }

    /**
     * =========================
     *  Handle Pagination Click
     * =========================
     */
    $(document).on("click", "#bim-pagination .page-link", function(e) {
        e.preventDefault();
        const page = $(this).data("page");
        loadBimFiles(page, currentSearch);
    });

    /**
     * =========================
     *  Handle Search Input
     * =========================
     */
    $("#search-bim").on("input", function() {
        currentSearch = $(this).val().trim();
        const page = $(this).data("page");
        loadBimFiles(page, currentSearch);
    });

    // Load initial files on page load
    const page = $(this).data("page");
    loadBimFiles(page, currentSearch);

});
