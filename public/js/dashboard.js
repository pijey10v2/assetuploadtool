$(document).ready(function() {

    $('#search-bim').on('keyup', function () {
        const query = $(this).val().toLowerCase();

        $('#bim-file-list .list-group-item').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
        });
    });

    $('#search-excel').on('keyup', function () {
        const query = $(this).val().toLowerCase();

        $('#excel-file-list .list-group-item').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
        });
    });

    let bimPage = 1;
    let excelPage = 1;

    loadFiles('bim');
    loadFiles('excel');

    // Load more event
    $('#load-more-bim').on('click', function() {
        bimPage++;
        loadFiles('bim', bimPage);
    });

    $('#load-more-excel').on('click', function() {
        excelPage++;
        loadFiles('excel', excelPage);
    });

    function loadFiles(type, page = 1, search) {
        const container = type === 'bim' ? '#bim-file-list' : '#excel-file-list';
        const loadMoreBtn = type === 'bim' ? '#load-more-bim' : '#load-more-excel';

        if (page === 1) $(container).html('<div class="text-center text-muted">Loading files...</div>');

        $.ajax({
            url: `/files/${type}?page=${page}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const files = response.files;

                    if (page === 1) $(container).empty();

                    files.forEach(file => {
                        $(container).append(`
                            <div class="justify-content-between align-items-center">
                                <div class="list-group-item ps-3">
                                    <strong>${file.name}</strong><br>
                                    <small class="text-muted">Modified: ${file.modified}</small>
                                    <span class="badge bg-light text-dark">${file.size}</span>
                                </div>
                            </div>
                        `);
                    });

                    const totalPages = Math.ceil(response.total / response.per_page);
                    if (page < totalPages) {
                        $(loadMoreBtn).removeClass('d-none');
                    } else {
                        $(loadMoreBtn).addClass('d-none');
                    }

                    if (response.total === 0) {
                        $(container).html('<div class="text-center text-muted">No files found.</div>');
                    }
                }
            },
            error: function() {
                $(container).html('<div class="text-danger text-center">Failed to load files.</div>');
            }
        });
    }
});

$(function () {
    function confirmAndClearFiles(url, listSelector, buttonSelector, fileType) {
        Swal.fire({
            title: `Clear all uploaded ${fileType}?`,
            text: `This will permanently remove all uploaded ${fileType.toLowerCase()}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear all',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $(buttonSelector)
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-1"></span> Clearing...');

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire({
                            title: 'Cleared!',
                            text: response.message,
                            icon: 'success',
                            timer: 2500,
                            showConfirmButton: false
                        });

                        $(listSelector).html(
                            `<div class="text-muted text-center py-3">No ${fileType.toLowerCase()} available.</div>`
                        );
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || `Failed to clear ${fileType}.`,
                            icon: 'error'
                        });
                    },
                    complete: function () {
                        $(buttonSelector)
                            .prop('disabled', false)
                            .html(`<i class="bi bi-trash me-1"></i> Clear Uploaded ${fileType}`);
                    }
                });
            }
        });
    }

    $('#clear-bim-files').on('click', function () {
        confirmAndClearFiles(window.fileRoutes.clearBim, '#bim-file-list', '#clear-bim-files', 'i.BIM Files');
    });

    $('#clear-excel-files').on('click', function () {
        confirmAndClearFiles(window.fileRoutes.clearExcel, '#excel-file-list', '#clear-excel-files', 'Excel Files');
    });
});
