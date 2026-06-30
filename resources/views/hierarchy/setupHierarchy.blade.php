@extends('layouts.app')

@section('content')

<style>
#mappingTable tbody tr{
    cursor:pointer;
}

#mappingTable tbody tr:hover{
    background:#eef6ff !important;
}
</style>

<div class="container-fluid">
    
    <h2>Setup Hierarchy</h2>

    <p>
        Import Batch:
        <strong>{{ $importBatch }}</strong>
    </p>

    <div class="mb-3">
        <button id="btnSaveMapping" class="btn btn-success">
            <i class="bi bi-save"></i>
            Save Mapping
        </button>
    </div>

    <div class="card mt-3 w-100">

        <div class="card-header">
            <h5 class="mb-0">Asset Hierarchy Mapping</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        Level 1
                    </label>

                    <select
                        id="globalLevel1"
                        class="form-select">
                        <option value="">
                            Select Level 1
                        </option>
                    </select>
                </div>
            </div>

            <div id="tableLoading" class="text-center py-5 d-none">
                <div class="spinner-border text-primary"></div>
                <div class="mt-2">
                    Loading hierarchy data...
                </div>
            </div>

                <table class="table table-bordered table-striped" id="mappingTable">
                    <thead class="table-light">
                    <tr>
                        <th>Element ID</th>
                        <th>Asset Name</th>
                        <th>Asset Code</th>
                        <th>Material</th>
                        <th>Material Code</th>
                        <th>Door Style</th>
                        <th>Level</th>
                        <th>Item No.</th>
                        <th width="220">Level 2</th>
                        <th width="220">Level 3</th>
                        <th width="220">Level 4</th>
                        <th width="220">Level 5</th>
                        <th width="220">Level 6</th>
                        <th width="120">Attributes</th>
                    </tr>
                    </thead>

                    <tbody>
                    </tbody>

                </table>
                
                <div class="modal fade"
                    id="attributeModal"
                    tabindex="-1">

                    <div class="modal-dialog modal-xl">

                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Asset Attributes
                                </h5>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal">
                                </button>
                            </div>

                            <div class="modal-body">

                                <table
                                    class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th>Attribute</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>

                                    <tbody id="attributeTableBody">
                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
window.routes = {
    getLevel1: "{{ route('hierarchy.get-level1') }}",
    getHierarchyAll: "{{ route('hierarchy.get-hierarchy-all') }}",
    getHierarchyData: "{{ route('hierarchy.get-hierarchy-data') }}",
    saveMapping: "{{ route('hierarchy.save-mapping') }}",
};
</script>
<script src="{{ asset('js/elementAttributes.js') }}"></script>
<script src="{{ asset('js/setupHierarchy.js') }}"></script>
@endpush

