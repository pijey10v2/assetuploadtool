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
                            <th>Level</th>
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
window.routes = {
    getLevel1: "{{ route('hierarchy.get-level1') }}",
    getHierarchyAll: "{{ route('hierarchy.get-hierarchy-all') }}",
    getHierarchyData: "{{ route('hierarchy.get-hierarchy-data') }}",
    saveMapping: "{{ route('hierarchy.save-mapping') }}",
};
</script>
<script>
$(document).ready(function () {

    let hierarchyData = [];
    let hierarchyMaster = [];
    let hierarchyLookup = {};
    let level1Data = [];

    let masterLoaded = false;
    let assetLoaded = false;

    // Load Level 1 dropdown data
    $.get(window.routes.getLevel1, function(response){

        level1Data = response.hierarchies || [];

        loadLevel1Dropdown();

    });

    // Load ALL hierarchy data
    $.get(window.routes.getHierarchyAll, function(response){

        hierarchyMaster = response.assets || [];

        hierarchyMaster.forEach(item => {

            let parentId =
                item.c_parent_id || 'root';

            if(!hierarchyLookup[parentId]) {
                hierarchyLookup[parentId] = [];
            }

            hierarchyLookup[parentId].push(item);

        });

        masterLoaded = true;

        initializeTable();

    });

    // Load table data
    loadHierarchy();

    function loadHierarchy()
    {
        $('#tableLoading').removeClass('d-none');
        $('#mappingTable').hide();

        $.ajax({
            url: window.routes.getHierarchyData,
            type: 'GET',

            success: function(response)
            {
                hierarchyData = response.assets || [];

                assetLoaded = true;

                initializeTable();
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
    function initializeTable()
    {
        if(!masterLoaded || !assetLoaded){
            return;
        }

        buildTable();

        autoPopulateMatchedLevels();

        if(window.selectedLevel1AfterSave)
        {
            $('#globalLevel1')
                .val(window.selectedLevel1AfterSave)
                .trigger('change');

            window.selectedLevel1AfterSave = null;
        }
    }
    function buildTable()
    {
        let tbody = $('#mappingTable tbody');

        tbody.empty();

        let html = '';

        hierarchyData.forEach(function(asset){

            html += `
            <tr
                data-id="${asset.id}"
                data-level1="${asset.c_level1_id || asset.c_matched_level1_id || ''}"
                data-level2="${asset.c_level2_id || asset.c_matched_level2_id || ''}"
                data-level3="${asset.c_level3_id || asset.c_matched_level3_id || ''}"
                data-level4="${asset.c_level4_id || asset.c_matched_level4_id || ''}"
            >
                <td>${asset.id ?? ''}</td>
                <td>${asset.c_element_id ?? ''}</td>
                <td>${asset.c_level ?? ''}</td>
                <td>${asset.c_item_no ?? ''}</td>

                <td>
                    <select class="form-select level2">
                        <option value="">
                            Select Level 2
                        </option>
                    </select>
                </td>

                <td>
                    <select class="form-select level3">
                        <option value="">
                            Select Level 3
                        </option>
                    </select>
                </td>

                <td>
                    <select class="form-select level4">
                        <option value="">
                            Select Level 4
                        </option>
                    </select>
                </td>
            </tr>
            `;
        });

        tbody.html(html);
    }

    function autoPopulateMatchedLevels()
    {
        $('#mappingTable tbody tr').each(function(){

            let row = $(this);

            let level1 = row.data('level1');
            let level2 = row.data('level2');
            let level3 = row.data('level3');
            let level4 = row.data('level4');

            console.log(
                'Row:',
                row.data('id'),
                level1,
                level2,
                level3,
                level4
            );

            if(!level1){
                return;
            }

            // Keep Level 1 selected
            if($('#globalLevel1').val() != level1){
                $('#globalLevel1').val(level1);
            }

            // ==========================
            // LEVEL 2
            // ==========================
            let level2Children =
                getChildren(level1);

            populateDropdown(
                row.find('.level2'),
                level2Children
            );

            if(
                level2 &&
                row.find('.level2 option[value="' + level2 + '"]').length
            ){
                row.find('.level2').val(level2);

                row.attr(
                    'data-level2',
                    level2
                );

                row.data(
                    'level2',
                    level2
                );
            }

            // ==========================
            // LEVEL 3
            // ==========================
            if(level2)
            {
                let level3Children =
                    getChildren(level2);

                populateDropdown(
                    row.find('.level3'),
                    level3Children
                );

                if(
                    level3 &&
                    row.find('.level3 option[value="' + level3 + '"]').length
                ){
                    row.find('.level3').val(level3);

                    row.attr(
                        'data-level3',
                        level3
                    );

                    row.data(
                        'level3',
                        level3
                    );
                }
            }

            // ==========================
            // LEVEL 4
            // ==========================
            if(level3)
            {
                let level4Children =
                    getChildren(level3);

                populateDropdown(
                    row.find('.level4'),
                    level4Children
                );

                if(
                    level4 &&
                    row.find('.level4 option[value="' + level4 + '"]').length
                ){
                    row.find('.level4').val(level4);

                    row.attr(
                        'data-level4',
                        level4
                    );

                    row.data(
                        'level4',
                        level4
                    );
                }
            }

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

        const level2Children =
            getChildren(level1Id);

        $('#mappingTable tbody tr').each(function(){

            let row = $(this);

            // Skip rows that already have values
            if(
                row.find('.level2').val() ||
                row.find('.level3').val() ||
                row.find('.level4').val()
            ){
                return;
            }

            populateDropdown(
                row.find('.level2'),
                level2Children
            );

            row.find('.level3').html(
                '<option value="">Select Level 3</option>'
            );

            row.find('.level4').html(
                '<option value="">Select Level 4</option>'
            );

        });

    });

    function getChildren(parentId)
    {
        return hierarchyLookup[parentId] || [];
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

   $(document).on(
        'change',
        '.level2',
        function(){

            let row =
                $(this).closest('tr');

            row.attr(
                'data-level2',
                $(this).val()
            );

            row.data(
                'level2',
                $(this).val()
            );

            let level3 =
                row.find('.level3');

            let level4 =
                row.find('.level4');

            let children =
                getChildren(
                    $(this).val()
                );

            populateDropdown(
                level3,
                children
            );

            level4.html(
                '<option value="">Select Level 4</option>'
            );
        }
    );

    $(document).on(
        'change',
        '.level3',
        function(){

            let row =
                $(this).closest('tr');

            row.attr(
                'data-level3',
                $(this).val()
            );

            row.data(
                'level3',
                $(this).val()
            );

            let level4 =
                row.find('.level4');

            let children =
                getChildren(
                    $(this).val()
                );

            populateDropdown(
                level4,
                children
            );
        }
    );

    $(document).on(
        'change',
        '.level4',
        function(){

            let row =
                $(this).closest('tr');

            row.attr(
                'data-level4',
                $(this).val()
            );

            row.data(
                'level4',
                $(this).val()
            );
        }
    );

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

            // const level2 =
            //     $(this).data('level2')
            //     || $(this).find('.level2').val();

            // const level3 =
            //     $(this).data('level3')
            //     || $(this).find('.level3').val();

            // const level4 =
            //     $(this).data('level4')
            //     || $(this).find('.level4').val();
            // // ONLY rows with selected hierarchy
            // if (!level2 && !level3 && !level4) {
            //     return;
            // }

            // mappings.push({
            //     id: $(this).data('id'),

            //     level1_id: level1Id,
            //     level1_name: level1Name,

            //     level2_id: level2,
            //     level2_name: $(this).find('.level2 option:selected').text(),

            //     level3_id: level3,
            //     level3_name: $(this).find('.level3 option:selected').text(),

            //     level4_id: level4,
            //     level4_name: $(this).find('.level4 option:selected').text()
            // });

            const level2 =
                $(this).data('level2')
                || $(this).find('.level2').val();

            const level3 =
                $(this).data('level3')
                || $(this).find('.level3').val();

            const level4 =
                $(this).data('level4')
                || $(this).find('.level4').val();

            if (!level2 && !level3 && !level4) {
                return;
            }
            const level2Text =
                $(this)
                .find('.level2 option:selected')
                .text() || '';

            const level3Text =
                $(this)
                .find('.level3 option:selected')
                .text() || '';

            const level4Text =
                $(this)
                .find('.level4 option:selected')
                .text() || '';

            mappings.push({
                id: $(this).data('id'),

                level1_id: level1Id,
                level1_name: level1Name,

                level2_id: level2,
                level2_name: level2Text,

                level3_id: level3,
                level3_name: level3Text,

                level4_id: level4,
                level4_name: level4Text
            });

        });

        console.log(mappings.length);

        $.ajax({

            url: window.routes.saveMapping,

            type: 'POST',

            data: {
                _token: '{{ csrf_token() }}',
                mappings: mappings
            },

            success: function (response) {

                alert('Mapping saved successfully.');

                let selectedLevel1 =
                    $('#globalLevel1').val();

                loadHierarchy();

                window.selectedLevel1AfterSave =
                    selectedLevel1;
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