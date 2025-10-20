@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-primary mb-1">Dashboard</h2>
            <p class="text-muted">Welcome back, {{ Auth::user()->name }} 👋</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-uppercase text-muted mb-1">BIM Files</h6>
                    <h3 class="fw-bold text-primary">{{ $summary['bimCount'] }}</h3>
                    <small class="text-muted">in repository</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-uppercase text-muted mb-1">Uploaded Excel Files</h6>
                    <h3 class="fw-bold text-success">{{ $summary['excelCount'] }}</h3>
                    <small class="text-muted">total processed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-uppercase text-muted mb-1">Last Upload</h6>
                    <h3 class="fw-bold text-warning">{{ $summary['lastUpload'] }}</h3>
                    <small class="text-muted">most recent Excel</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6 class="text-uppercase text-muted mb-1">Pending Tasks</h6>
                    <h3 class="fw-bold text-danger">{{ $summary['pendingTasks'] }}</h3>
                    <small class="text-muted">to review</small>
                </div>
            </div>
        </div>
    </div>

    <!-- File Tables -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-box-seam me-2 text-primary"></i> BIM Files
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>File Name</th>
                                <th>Size (KB)</th>
                                <th>Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bimFiles as $file)
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>{{ $file['size'] }}</td>
                                    <td>{{ $file['modified'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No BIM files found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-file-earmark-excel me-2 text-success"></i> Uploaded Excel Files
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>File Name</th>
                                <th>Size (KB)</th>
                                <th>Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($excelFiles as $file)
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>{{ $file['size'] }}</td>
                                    <td>{{ $file['modified_exact'] }} <small class="text-muted">({{ $file['modified_human'] }})</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No Excel files found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
