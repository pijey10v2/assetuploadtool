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