@extends('layouts.app')

@section('content')
<div class="container">
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

    <div class="card mt-3">
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
                            <th>ID</th>
                            <th>Element ID</th>
                            <th>Item No.</th>
                            <th width="220">Level 2</th>
                            <th width="220">Level 3</th>
                            <th width="220">Level 4</th>
                        </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    let hierarchyData = [];
    let hierarchyMaster = [];
    let level1Data = [];

    // Load Level 1 dropdown data
    $.get('/hierarchy/get-level1', function(response){

        level1Data = response.hierarchies || [];

        loadLevel1Dropdown();

    });

    // Load ALL hierarchy data
    $.get('/hierarchy/get-hierarchy-all', function(response){

        hierarchyMaster = response.assets || [];

        console.log(
            'Hierarchy Master Loaded:',
            hierarchyMaster.length
        );

    });

    // Load table data
    loadHierarchy();

    function loadHierarchy()
    {
        $('#tableLoading').removeClass('d-none');
        $('#mappingTable').hide();

        $.ajax({
            url: '/hierarchy/get-data',
            type: 'GET',

            success: function(response)
            {
                hierarchyData = response.assets || [];

                buildTable();
            },

            error: function(err)
            {
                console.error(err);
                alert('Unable to load hierarchy data.');
            },

            complete: function()
            {
                $('#tableLoading').addClass('d-none');
                $('#mappingTable').show();
            }
        });
    }
    function buildTable()
    {
        let tbody = $('#mappingTable tbody');

        tbody.empty();

        hierarchyData.forEach(function(asset, index){

            let row = `
            <tr data-id="${asset.id}">
                <td>${asset.id ?? ''}</td>
                <td>${asset.c_element_id ?? ''}</td>
                <td>${asset.c_item_no ?? ''}</td>

                <td>
                    <select class="form-select level2">
                        <option value="">Select Level 2</option>
                    </select>
                </td>

                <td>
                    <select class="form-select level3">
                        <option value="">Select Level 3</option>
                    </select>
                </td>

                <td>
                    <select class="form-select level4">
                        <option value="">Select Level 4</option>
                    </select>
                </td>
            </tr>
            `;

            tbody.append(row);

        });

    }

    function loadLevel1Dropdown()
    {
        let dropdown = $('#globalLevel1');

        dropdown.html(
            '<option value="">Select Level 1</option>'
        );

        level1Data.forEach(item => {

            dropdown.append(`
                <option value="${item.id}">
                    ${item.c_asset_name}
                </option>
            `);

        });
    }

    $('#globalLevel1').on('change', function(){

        const level1Id = $(this).val();

        console.log('Level1 ID:', level1Id);

        const children = getChildren(level1Id);

        console.log(children);

        $('.level2').each(function(){

            populateDropdown($(this), children);

        });

        $('.level3').html(
            '<option value="">Select Level 3</option>'
        );

        $('.level4').html(
            '<option value="">Select Level 4</option>'
        );

    });

    function getChildren(parentId)
    {
        return hierarchyMaster.filter(item =>
            item.c_parent_id == parentId
        );
    }

    function populateDropdown(select, children)
    {
        select.empty();

        select.append(
            '<option value="">Select</option>'
        );

        children.forEach(item => {

            select.append(`
                <option value="${item.id}">
                    ${item.c_asset_name}
                </option>
            `);

        });
    }

    $(document).on('change', '.level2', function(){

        let row = $(this).closest('tr');

        let level3 = row.find('.level3');
        let level4 = row.find('.level4');

        level4.html('<option value="">Select Level 4</option>');

        let children = getChildren($(this).val());

        populateDropdown(level3, children);
    });

    $(document).on('change', '.level3', function(){

        let row = $(this).closest('tr');

        let level4 = row.find('.level4');

        let children = getChildren($(this).val());

        populateDropdown(level4, children);
    });

    $('#btnSaveMapping').on('click', function () {

        const button = $(this);

        const level1Id = $('#globalLevel1').val();
        const level1Name = $('#globalLevel1 option:selected').text();

        if (!level1Id) {
            alert('Please select Level 1 first.');
            return;
        }

        button.prop('disabled', true);

        button.html(`
            <span class="spinner-border spinner-border-sm me-2"></span>
            Saving...
        `);

        let mappings = [];

        $('#mappingTable tbody tr').each(function () {

            const level2 = $(this).find('.level2').val();
            const level3 = $(this).find('.level3').val();
            const level4 = $(this).find('.level4').val();

            // ONLY rows with selected hierarchy
            if (!level2 && !level3 && !level4) {
                return;
            }

            mappings.push({
                id: $(this).data('id'),

                level1_id: level1Id,
                level1_name: level1Name,

                level2_id: level2,
                level2_name: $(this).find('.level2 option:selected').text(),

                level3_id: level3,
                level3_name: $(this).find('.level3 option:selected').text(),

                level4_id: level4,
                level4_name: $(this).find('.level4 option:selected').text()
            });

        });

        console.log(mappings.length);

        $.ajax({

            url: '/hierarchy/save-mapping',

            type: 'POST',

            data: {
                _token: '{{ csrf_token() }}',
                mappings: mappings
            },

            success: function (response) {

                alert('Mapping saved successfully.');

                loadHierarchy();

            },

            error: function (xhr) {

                console.error(xhr);

                alert('Save failed.');

            },

            complete: function () {

                button.prop('disabled', false);

                button.html(`
                    <i class="bi bi-save"></i>
                    Save Mapping
                `);

            }

        });

    });

});
</script>
@endsection