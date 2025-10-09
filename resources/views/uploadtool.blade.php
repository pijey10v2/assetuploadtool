@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center">
    <div class="card shadow-sm p-4" style="max-width: 500px; width: 100%;">
        <h3 class="mb-4 text-center">Upload Tool</h3>

        <form action="{{ route('uploadtool.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- BIM File -->
            <div class="mb-3">
                <label for="bimfile" class="form-label">BIM File <span class="text-danger">*</span></label>
                <input class="form-control" type="file" id="bimfile" name="bimfile" required>
                <small class="form-text text-muted">Upload the BIM file (required)</small>
            </div>

            <!-- RAW File -->
            <div class="mb-3">
                <label for="rawfile" class="form-label">RAW File <span class="text-danger">*</span></label>
                <input class="form-control" type="file" id="rawfile" name="rawfile" required>
                <small class="form-text text-muted">Upload the RAW file (required)</small>
            </div>

            <!-- Import Batch No -->
            <div class="mb-3">
                <label for="import_batch_no" class="form-label">Import Batch No <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="import_batch_no" 
                    name="import_batch_no" 
                    placeholder="Enter import batch number" 
                    required>
            </div>

            <!-- Data ID -->
            <div class="mb-3">
                <label for="data_id" class="form-label">Data ID <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="data_id" 
                    name="data_id" 
                    placeholder="Enter data ID" 
                    required>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
