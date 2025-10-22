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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="text-center text-muted">No mapping data available.</td>
                </tr>
            </tbody>
        </table>

        <!-- Buttons Row -->
        <div class="d-flex justify-content-between mt-3">
            <button id="execute-update" type="button" class="btn btn-success btn-sm" disabled>
                <i class="bi bi-play-circle me-1"></i> Execute Data Update
            </button>
        </div>
        
        <!-- Spinner Container -->
        <div id="execute-loading-container" class="mt-3 text-center" style="display: none;">
            <div class="spinner-border text-success mb-2" role="status" style="width: 2rem; height: 2rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div id="execute-loading-message" class="fw-semibold text-muted">
                Please wait while the data is being inserted/updated...
            </div>
        </div>

        <!-- Status -->
        <div id="execute-status" class="mt-3"></div>
    </div>
</div>
