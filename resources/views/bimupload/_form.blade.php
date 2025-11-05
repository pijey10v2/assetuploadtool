<div class="d-flex justify-content-center">
    <form id="bimUploadForm" class="w-50" enctype="multipart/form-data">
        @csrf
        <div class="mb-3 text-center">
            <label for="bimfile" class="form-label fw-bold">Select i.BIM Files</label>
            <input type="file" id="bimfile" name="bimfiles[]" class="form-control" accept=".bim" multiple required>
            <div class="form-text">You can select multiple files (.bim).</div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Selected Files
            </button>
        </div>

        <div class="progress mb-3" id="progress-container" style="height: 20px; display: none;">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%">0%</div>
        </div>

        <div id="upload-status"></div>
    </form>
</div>
