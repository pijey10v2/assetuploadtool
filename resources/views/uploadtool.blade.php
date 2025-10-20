@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row g-4 align-items-start">
        <!-- Left Column: Upload Form -->
        <div class="col-lg-4 col-md-5">
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

                    <!-- BIM File -->
                     
                    <!-- <div class="mb-3">
                        <label for="bimfile" class="form-label">Select BIM File <span class="text-danger">*</span></label>
                        <select class="form-select" id="bimfile" name="bimfile" required>
                            <option value="">-- Select BIM File --</option>
                            @foreach($bimFiles as $file)
                                <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a BIM file.</div>
                    </div> -->

                    <div class="mb-3">
                        <label for="bimfile" class="form-label">BIM File <span class="text-danger">*</span></label>
                        <select class="form-select" id="bimfile" name="bimfile" required>
                            <option value="">-- Select BIM File --</option>
                            @foreach($bimFiles as $file)
                                @php
                                    $size = Storage::size('bimfiles/' . $file);
                                    $sizeKb = round($size / 1024, 2);
                                @endphp
                                <option value="{{ $file }}">{{ $file }} ({{ $sizeKb }} KB)</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a BIM file.</div>
                    </div>



                    <!-- RAW File -->
                    <div class="mb-3">
                        <label for="rawfile" class="form-label">RAW File <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="rawfile" name="rawfile"
                            accept=".xlsx,.xls,.csv" required>
                        <div class="invalid-feedback">Please select a valid Excel file (.xlsx / .xls / .csv).</div>
                    </div>

                    <!-- Import Batch No -->
                    <div class="mb-3">
                        <label for="import_batch_no" class="form-label">Import Batch No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="import_batch_no" name="import_batch_no"
                            placeholder="Enter import batch number" required>
                        <div class="invalid-feedback">Please enter an import batch number.</div>
                    </div>

                    <!-- Data ID -->
                    <div class="mb-3">
                        <label for="data_id" class="form-label">Data ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="data_id" name="data_id"
                            placeholder="Enter data ID" required>
                        <div class="invalid-feedback">Please enter a data ID.</div>
                    </div>

                    <!-- Mapping Source -->
                    <div class="mb-3">
                        <label for="asset_table_name" class="form-label">Asset Table <span class="text-danger">*</span></label>
                         <select class="form-select" id="asset_table_name" name="asset_table_name" required>
                            <option selected disabled><span class="spinner-border spinner-border-sm"></span> Loading...</option>
                        </select>
                        <div class="invalid-feedback">Please select a mapping type.</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="processBtn">Process</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Mapping Table -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm p-4 h-100">
                <h4 class="text-center mb-4">Database & Excel Columns Mapping</h4>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered align-middle text-center" id="mapping-table">
                        <thead class="table-light">
                            <tr>
                                <th>Database Columns</th>
                                <th>Excel Columns (Raw File)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="text-muted">Mapping data will appear here after processing.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Execute Button -->
                <div class="text-center mt-4" id="execute-btn-container" style="display:none;">
                    <button id="executeBtn" class="btn btn-success px-4 py-2">Execute Data Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    // Handle form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Bootstrap validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            $(form).addClass('was-validated');
            return false;
        }

        const formData = new FormData(form);

        // Disable button & show spinner
        $('#processBtn')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: "{{ route('uploadtool.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#progress-container').show();
                $('#progress-bar')
                    .removeClass('bg-danger bg-success')
                    .addClass('bg-info progress-bar-striped progress-bar-animated')
                    .css('width', '100%')
                    .text('Processing files, please wait...');
                $('#upload-status').html('');
            },
            success: function(response) {
                $('#progress-bar')
                    .removeClass('bg-info progress-bar-striped progress-bar-animated')
                    .addClass('bg-success')
                    .text('Completed');

                $('#upload-status').html(`
                    <div class="alert alert-success mt-3">
                        ${response.message}<br>
                        BIM Rows Found: <strong>${response.bim_count}</strong>
                    </div>
                `);

                renderMappingTable(response.db_columns, response.raw_columns);

                // Show Execute button
                $('#execute-btn-container').fadeIn(600);

                // Reset upload button text
                $('#processBtn')
                    .prop('disabled', false)
                    .text('Process Again');

                form.reset();
                $(form).removeClass('was-validated');
            },
            error: function(xhr, status, error) {
                let msg = xhr.responseJSON?.message || error;
                $('#progress-bar')
                    .removeClass('bg-info progress-bar-striped progress-bar-animated')
                    .addClass('bg-danger')
                    .text('Error');

                $('#upload-status').html(`
                    <div class="alert alert-danger mt-3">
                        Upload failed: ${msg}
                    </div>
                `);

                $('#processBtn')
                    .prop('disabled', false)
                    .text('Process');
            }
        });
    });

    // Execute Data Update Button Handler
    $('#executeBtn').on('click', function() {
        alert('Executing data update...');
    });

    // Render Mapping Table
    function renderMappingTable(dbCols, excelCols) {
        const tbody = $('#mapping-table tbody');
        tbody.empty();

        const maxRows = Math.max(dbCols.length, excelCols.length);

        if (maxRows === 0) {
            tbody.append(`
                <tr>
                    <td colspan="2" class="text-muted">No mapping data available.</td>
                </tr>
            `);
            return;
        }

        for (let i = 0; i < maxRows; i++) {
            tbody.append(`
                <tr>
                    <td>${dbCols[i] ? dbCols[i] : ''}</td>
                    <td>${excelCols[i] ? excelCols[i] : ''}</td>
                </tr>
            `);
        }
    }
});
</script>

<script>
    const API_GET_ALL_TABLES_URL = "<?php echo $_ENV['API_GET_ALL_TABLES_URL']; ?>";
    async function loadAssetTables() {
      const dropdown = document.getElementById("asset_table_name");

      try {
        const response = await fetch(API_GET_ALL_TABLES_URL);
        const data = await response.json();

        if (data.status === "success" && Array.isArray(data.tables)) {
          dropdown.innerHTML = '<option value="" selected disabled>Select a table</option>';
          data.tables.forEach(table => {
            const option = document.createElement("option");
            option.value = table;
            option.textContent = table;
            dropdown.appendChild(option);
          });
        } else {
          dropdown.innerHTML = '<option disabled>Error loading tables</option>';
          console.error("Error:", data.message);
        }
      } catch (error) {
        dropdown.innerHTML = '<option disabled>Failed to fetch tables</option>';
        console.error("Fetch error:", error);
      }
    }

    // Load tables when the page loads
    document.addEventListener("DOMContentLoaded", loadAssetTables);
  </script>
@endpush
