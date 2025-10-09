@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center mt-5">
    <div class="card shadow-sm p-4" style="max-width: 500px; width: 100%;">
        <h3 class="mb-4 text-center">Upload Tool</h3>

        <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 20px; display: none;" id="progress-container">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width:0%">0%</div>
        </div>

        <!-- Status Message -->
        <div id="upload-status" class="mb-3"></div>

        <!-- Upload Form -->
        <form id="uploadForm" class="needs-validation" novalidate>
            @csrf

            <!-- BIM File -->
            <div class="mb-3">
                <label for="bimfile" class="form-label">BIM File <span class="text-danger">*</span></label>
                <input class="form-control" type="file" id="bimfile" name="bimfile" required>
                <div class="invalid-feedback">Please select a BIM file.</div>
            </div>

            <!-- RAW File -->
            <div class="mb-3">
                <label for="rawfile" class="form-label">RAW File <span class="text-danger">*</span></label>
                <input class="form-control" type="file" id="rawfile" name="rawfile" required>
                <div class="invalid-feedback">Please select a RAW file.</div>
            </div>

            <!-- Import Batch No -->
            <div class="mb-3">
                <label for="import_batch_no" class="form-label">Import Batch No <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="import_batch_no" name="import_batch_no" placeholder="Enter import batch number" required>
                <div class="invalid-feedback">Please enter an import batch number.</div>
            </div>

            <!-- Data ID -->
            <div class="mb-3">
                <label for="data_id" class="form-label">Data ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="data_id" name="data_id" placeholder="Enter data ID" required>
                <div class="invalid-feedback">Please enter a data ID.</div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();

        var form = this;

        // Bootstrap validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            return false; // stop AJAX if invalid
        }

        var formData = new FormData(form);

        // Reset progress and status
        $('#progress-container').show();
        $('#progress-bar').removeClass('bg-danger').addClass('bg-success').css('width','0%').text('0%');
        $('#upload-status').html('');

        $.ajax({
            url: 'http://localhost:3000/api/upload-bim', // Node.js API
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100);
                        $('#progress-bar').css('width', percent + '%').text(percent + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                $('#upload-status').html(`
                    <div class="alert alert-success">
                         ${response.message}<br>
                        Inserted: <strong>${response.inserted}</strong>
                    </div>
                `);
                form.reset();
                $(form).removeClass('was-validated');
                $('#progress-bar').css('width','100%').text('100%');
            },
            error: function(xhr, status, error) {
                let msg = xhr.responseJSON?.message || error;
                $('#upload-status').html(`
                    <div class="alert alert-danger">
                         Upload failed: ${msg}
                    </div>
                `);
                $('#progress-bar').removeClass('bg-success').addClass('bg-danger').text('Error');
            }
        });
    });

});
</script>
@endpush
