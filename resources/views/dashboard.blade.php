@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-primary mb-1">Welcome, {{ Auth::user()->name }} 👋</h2>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Uploaded i.BIM Files</h6>
                    <h3 class="fw-bold text-primary">{{ $summary['bimCount'] }}</h3>
                    <small class="text-muted">total uploaded (in directory \storage\app\bimfiles)</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Uploaded Excel Files</h6>
                    <h3 class="fw-bold text-success">{{ $summary['excelCount'] }}</h3>
                    <small class="text-muted">total processed (in directory \storage\app\uploads)</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Last Upload</h6>
                    <h3 class="fw-bold text-warning">{{ $summary['lastUpload'] }}</h3>
                    <small class="text-muted">most recent Excel</small>
                </div>
            </div>
        </div>
    </div>

    <!-- File Tables -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <!-- Left side: icon + title -->
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box text-primary me-2"></i>
                        <h6 class="mb-0 fw-bold">Uploaded i.BIM Files</h6>
                    </div>

                    <!-- Right side: clear button -->
                    <button id="clear-bim-files" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Clear Uploaded i.BIM Files
                    </button>
                </div>
                <div class="card-body p-0">
                    <input type="text" id="search-bim" class="form-control mb-2" placeholder="Search i.BIM Files...">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <div id="bim-file-list" class="list-group">
                        <div class="text-center text-muted">Loading files...</div>
                    </div>
                    <button id="load-more-bim" class="btn btn-outline-primary btn-sm mt-3 d-none">Load More</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <!-- Left side: icon + title -->
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                        <h6 class="mb-0 fw-bold">Uploaded Excel Files</h6>
                    </div>

                    <!-- Right side: clear button -->
                    <button id="clear-excel-files" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Clear Uploaded Excel Files
                    </button> 
                </div>
                <div class="card-body p-0">
                    <input type="text" id="search-excel" class="form-control mb-2" placeholder="Search Excel files...">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <div id="excel-file-list" class="list-group">
                        <div class="text-center text-muted">Loading files...</div>
                    </div>
                    <button id="load-more-excel" class="btn btn-outline-primary btn-sm mt-3 d-none">Load More</button>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.fileRoutes = {
        clearBim: "{{ route('files.clearBim') }}",
        clearExcel: "{{ route('files.clearExcel') }}"
    };
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush