@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2 text-primary"></i> Upload i.BIM File</h4>
            <button id="clear-bim-files" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash"></i> Clear All i.BIM Files
            </button>
        </div>

        {{-- BIM Upload Form --}}
        @include('bimupload._form')

        <hr>

        {{-- Uploaded BIM File List --}}
        @include('bimupload._filelist')
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.uploadToolConfig = {
            routes: {
                storeBim: "{{ route('bimupload.store') }}",
                clearBim: "{{ route('bimupload.clearBim') }}",
                clearBimFiles: "{{ route('bimupload.clearBimFiles') }}",
                listBimFiles: "{{ route('bimupload.listBim') }}"
            },
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('js/bimupload.js') }}"></script>
@endpush

