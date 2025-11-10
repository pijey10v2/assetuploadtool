<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">
        <i class="bi bi-folder2-open text-primary me-2"></i> Uploaded i.BIM Files
    </h6>

    <div class="d-flex align-items-center gap-3">
        <button id="delete-selected-bim" class="btn btn-danger btn-sm">
            <i class="bi bi-trash3"></i> Clear Selected Files
        </button>
    </div>
</div>

<small id="bimfile-counter" class="text-muted">Loading...</small>

<!-- Search Bar -->
<div class="input-group">
    <span class="input-group-text"><i class="bi bi-search"></i></span>
    <input type="text" id="search-bim" class="form-control" placeholder="Search BIM files...">
</div>

<!-- Data Table -->
<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 5%; text-align:center;">
                    <input type="checkbox" id="check-all-bim">
                </th>
                <th>File Name</th>
                <th>Date Uploaded</th>
                <th>Uploaded By</th>
            </tr>
        </thead>
        <tbody id="bim-file-list">
            <tr>
                <td colspan="4" class="text-center text-muted">No files found.</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="text-center mt-3">
    <nav class="mt-3" id="bim-pagination"></nav>
</div>


