$(document).ready(function() {

    const routes = window.fileRoutes.routes;

    // Load Files
    let bimPage = 1, excelPage = 1;
    loadFiles('bim');
    loadFiles('excel');

    $('#load-more-bim').on('click', function() {
        bimPage++;
        loadFiles('bim', bimPage);
    });

    $('#load-more-excel').on('click', function() {
        excelPage++;
        loadFiles('excel', excelPage);
    });

    function loadFiles(type, page = 1, search = '') {
        const container = type === 'bim' ? '#bim-file-list' : '#excel-file-list';
        const loadMoreBtn = type === 'bim' ? '#load-more-bim' : '#load-more-excel';
        const url = type === 'bim' ? routes.listBimFiles : routes.listExcelFiles;

        if (page === 1) $(container).html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');

        $.ajax({
            url: `${url}?page=${page}&search=${encodeURIComponent(search)}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    if (page === 1) $(container).empty();

                    response.files.forEach(file => {
                        $(container).append(`
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="${type}-checkbox file-checkbox" data-filename="${file.name}">
                                </td>
                                <td>${file.name}</td>
                            </tr>
                        `);
                    });


                    const totalPages = Math.ceil(response.total / response.per_page);
                    $(loadMoreBtn).toggleClass('d-none', page >= totalPages);
                    
                    if (response.total === 0) {
                        $(container).html('<tr><td colspan="4" class="text-center text-danger">No files found.</td></tr>');
                    }
                }
            },
            error: function() {
                $(container).html('<tr><td colspan="4" class="text-center text-danger">Failed to load files.</td></tr>');
            }
        });
    }

    // Select All
    $(document).on('change', '#check-all-bim', function() {
        $('.bim-checkbox').prop('checked', this.checked);
    });

    $(document).on('change', '#check-all-excel', function() {
        $('.excel-checkbox').prop('checked', this.checked);
    });

    // Search
    $('#search-bim').on('keyup', function () {
        const query = $(this).val();
        loadFiles('bim', 1, query);
    });

    $('#search-excel').on('keyup', function () {
        const query = $(this).val();
        loadFiles('excel', 1, query);
    });

    // Clear Selected
    function clearSelected(type) {
        // Get selected filenames
        const selected = $(`.${type}-checkbox:checked`).map(function () {
            return $(this).data('filename');
        }).get();

        console.log(`Selected ${type} files:`, selected); // Debug line (check console)

        // Check if any selected
        if (!selected.length) {
            Swal.fire('No files selected', `Please select ${type.toUpperCase()} files to clear.`, 'info');
            return;
        }

        const clearRoute = type === 'bim'
            ? routes.clearSelectedBim
            : routes.clearSelectedExcel;

        // Confirmation dialog
        Swal.fire({
            title: `Are you sure you want to delete ${selected.length} file(s)?`,
            text: `This will permanently delete the selected ${type.toUpperCase()} files.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: clearRoute,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': window.fileRoutes.csrfToken },
                    data: { files: selected },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadFiles(type, 1); // reload list
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete files.', 'error');
                    }
                });
            }
        });
    }

    $('#clear-selected-bim').on('click', function () { clearSelected('bim'); });
    $('#clear-selected-excel').on('click', function () { clearSelected('excel'); });

    // Clear All
    function clearAll(type) {
        const clearRoute = type === 'bim' ? routes.clearBim : routes.clearExcel;

        Swal.fire({
            title: `Clear all ${type.toUpperCase()} files?`,
            text: `This will permanently remove all uploaded ${type.toUpperCase()} files.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, clear all',
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: clearRoute,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': window.fileRoutes.csrfToken },
                    success: function(response) {
                        Swal.fire('Cleared!', response.message, 'success');
                        loadFiles(type, 1);
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to clear files.', 'error');
                    }
                });
            }
        });
    }

    $('#clear-bim-files').on('click', function () { clearAll('bim'); });
    $('#clear-excel-files').on('click', function () { clearAll('excel'); });
});
