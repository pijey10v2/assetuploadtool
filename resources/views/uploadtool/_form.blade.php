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

        <!-- i.BIM File -->
        <div class="mb-3">
            <label for="bimfile" class="form-label">Select i.BIM File <span class="text-danger">*</span></label>
            <select class="form-select" id="bimfile" name="bimfile" required>
                <option value="">Select i.BIM File...</option>
                @foreach($bimFiles as $file)
                    <option value="{{ $file }}">{{ $file }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">Please select a i.BIM file.</div>
        </div>

        <!-- RAW File -->
        <div class="mb-3">
            <label for="rawfile" class="form-label">RAW File <span class="text-danger">*</span></label>
            <input class="form-control" type="file" id="rawfile" name="rawfile"
                accept=".xlsx,.xls" required>
            <div class="invalid-feedback">Please select a valid Excel file (.xlsx / .xls).</div>
        </div>

        <div class="mb-3">
            <label for="project_id" class="form-label">Select Project <span class="text-danger">*</span></label>
            <select class="form-select" id="project_id" name="project_id" required>
                <option value="">Select Project...</option>
                <!-- Populated by AJAX -->
            </select>
            <div class="invalid-feedback">Please select a project.</div>
        </div>

        <div class="mb-3" id="layer-wrapper" style="display:none;">
            <label for="data_id" class="form-label">Select Layer Name (Data ID) <span class="text-danger">*</span></label>
            <select class="form-select" id="data_id" name="data_id" required>
                <option value="">Select Layer...</option>
                <!-- Populated dynamically -->
            </select>
            <div class="invalid-feedback">Please select a layer.</div>
        </div>

        <!-- Asset Table -->
        <div class="mb-3">
            <label for="asset_table_name" class="form-label">Asset Table <span class="text-danger">*</span></label>
            <select class="form-select" id="asset_table_name" name="asset_table_name" required>
                <option selected disabled><span class="spinner-border spinner-border-sm"></span> Loading...</option>
            </select>
            <div class="invalid-feedback">Please select an asset table.</div>
        </div>

        <!-- Import Batch No -->
        <div class="mb-3">
            <label for="import_batch_no" class="form-label">Import Batch No</label>
            <input type="text" class="form-control" id="import_batch_no" name="import_batch_no"
           placeholder="Auto-generated after clicking process button..." readonly>
        </div>

        <!-- Submit -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary" id="processBtn">Process</button>
        </div>
    </form>
</div>
