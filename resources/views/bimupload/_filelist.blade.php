<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">
        <i class="bi bi-folder2-open text-primary me-2"></i> Uploaded i.BIM Files
    </h6>

    <div class="d-flex align-items-center gap-3">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="check-all-bim">
            <label class="form-check-label small" for="check-all-bim">Check All Selected</label>
        </div>
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

<div id="bim-file-list" class="mt-3"></div>

<!-- Pagination Container -->
<nav class="mt-3" id="bim-pagination"></nav>
