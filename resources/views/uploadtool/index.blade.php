@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row g-4 align-items-start">
        
        <!-- Left Column: Upload Form -->
        <div class="col-lg-4 col-md-5">
            @include('uploadtool._form')
        </div>

        <!-- Right Column: Mapping Table -->
        <div class="col-lg-8 col-md-7">
            @include('uploadtool._mapping-table')
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.uploadToolConfig = {
            routes: {
                store: "{{ route('uploadtool.store') }}",
                execute: "{{ url('/uploadtool/execute-update') }}",
                progress: "{{ url('/uploadtool/progress') }}"
            },
            api: {
                getAllTables: "{{ env('API_GET_ALL_TABLES_URL') }}",
                getProjects: "{{ route('api.projects') }}",
                getLayersByProject: "{{ url('/dropdown/layers') }}" // base URL, append project ID in JS
            }
        };
    </script>
    <script src="{{ asset('js/uploadtool.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
@endpush
