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

        const urlParams = new URLSearchParams(window.location.search);
        const importBatch = urlParams.get('import_batch');

        $.ajax({
            url: window.routes.getHierarchyData,
            type: 'GET',
            data: {
                import_batch: importBatch
            },

            success: function(response)
            {
                hierarchyData = response.assets || [];

                assetLoaded = true;

                initializeTable();
            },

            error: function(err)
            {
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

        const hasMatchedLevels =
            hierarchyData.some(x =>
                x.c_matched_level1_id ||
                x.c_matched_level2_id ||
                x.c_matched_level3_id ||
                x.c_matched_level4_id
            );

        if(hasMatchedLevels)
        {
            autoPopulateMatchedLevels();
        }
        else
        {
            autoMatchAllRows();
        }

        if(window.selectedLevel1AfterSave)
        {
            $('#globalLevel1')
                .val(window.selectedLevel1AfterSave)
                .trigger('change');

            window.selectedLevel1AfterSave = null;
        }
    }
    function getChildren(parentId)
    {
        return hierarchyLookup[parentId] || [];
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

                data-asset="${JSON.stringify(asset)}"

                data-keywords="${asset.c_keywords || ''}"

                data-level1="${asset.c_level1_id || asset.c_matched_level1_id || ''}"

                data-level2="${asset.c_level2_id || asset.c_matched_level2_id || ''}"

                data-level3="${asset.c_level3_id || asset.c_matched_level3_id || ''}"

                data-level4="${asset.c_level4_id || asset.c_matched_level4_id || ''}"
            >
                <td>${asset.c_element_id ?? ''}</td>
                <td>${asset.c_asset_name ?? ''}</td>
                <td>${asset.c_asset_code ?? ''}</td>
                <td>${asset.c_material ?? ''}</td>
                <td>${asset.c_material_code ?? ''}</td>
                <td>${asset.c_door_style ?? ''}</td>
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
                <td>
                <button
                    class="btn btn-sm btn-info btnAttributes"
                    data-id="${asset.id}">
                    View
                </button>
            </td>
            </tr>
            `;
        });

        tbody.html(html);
    }
    function getHierarchyById(id)
    {
        return hierarchyMaster.find(
            x => x.id === id
        );
    }

    function buildHierarchyChain(id)
    {
        let chain = [];

        let current =
            getHierarchyById(id);

        while(current)
        {
            chain.unshift(current);

            if(
                !current.c_parent_id ||
                current.c_parent_id === ''
            ){
                break;
            }

            current =
                getHierarchyById(
                    current.c_parent_id
                );
        }

        return chain;
    }

    function autoPopulateMatchedLevels()
    {
        $('#mappingTable tbody tr').each(function(){

            const row = $(this);

            let level1 =
                row.data('level1');

            let level2 =
                row.data('level2');

            let level3 =
                row.data('level3');

            let level4 =
                row.data('level4');

            // deepest available match
            const deepest =
                level4 ||
                level3 ||
                level2 ||
                level1;

            if(!deepest){
                return;
            }

            const chain =
                buildHierarchyChain(
                    deepest
                );

            chain.forEach(item => {

                const level =
                    parseInt(
                        item.c_level || 0
                    );

                switch(level)
                {
                    case 1:
                        level1 = item.id;
                        break;

                    case 2:
                        level2 = item.id;
                        break;

                    case 3:
                        level3 = item.id;
                        break;

                    case 4:
                        level4 = item.id;
                        break;
                }
            });

            console.log(
                'Resolved:',
                level1,
                level2,
                level3,
                level4
            );

           // =====================
            // Level 1
            // =====================

            if(
                level1 &&
                $('#globalLevel1 option[value="' + level1 + '"]').length
            ){
                $('#globalLevel1').val(level1);
            }

            // =====================
            // Level 2
            // =====================

            if(!level1 && level2)
            {
                const level2Node =
                    getHierarchyById(level2);

                if(level2Node)
                {
                    level1 = level2Node.c_parent_id;

                    row.attr('data-level1', level1);
                    row.data('level1', level1);
                }
            }

            if(!level1){
                return;
            }

            const level2Children =
                getChildren(level1);

            populateDropdown(
                row.find('.level2'),
                level2Children
            );

            if(level2)
            {
                row.find('.level2')
                    .val(level2);
            }

            // =====================
            // Level 3
            // =====================

            if(level2)
            {
                const level3Children =
                    getChildren(level2);

                populateDropdown(
                    row.find('.level3'),
                    level3Children
                );

                if(!level3)
                {
                    const keywords =
                        row.data('keywords') || '';

                    const bestLevel3 =
                        findBestMatch(
                            keywords,
                            level3Children
                        );

                    if(bestLevel3)
                    {
                        level3 = bestLevel3.id;

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

                if(level3)
                {
                    row.find('.level3').val(level3);
                }
            }

            // =====================
            // Level 4
            // =====================

            if(level3)
            {
                const level4Children =
                    getChildren(level3);

                populateDropdown(
                    row.find('.level4'),
                    level4Children
                );

                if(!level4)
                {
                    const keywords =
                        row.data('keywords') || '';

                    const bestLevel4 =
                        findBestMatch(
                            keywords,
                            level4Children
                        );

                    if(bestLevel4)
                    {
                        level4 = bestLevel4.id;

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

                if(level4)
                {
                    row.find('.level4').val(level4);
                }
            }

            row.data('level1', level1);
            row.data('level2', level2);
            row.data('level3', level3);
            row.data('level4', level4);
        });
    }

    function loadLevel1Dropdown()
    {
        let dropdown = $('#globalLevel1');

        dropdown.html(
            '<option value="">Select Level 1</option>'
        );

        console.log('LEVEL1 DATA', level1Data);

        level1Data.forEach(item => {

            dropdown.append(`
                <option value="${item.id}">
                    ${item.c_asset_name}
                </option>
            `);

        });

        console.log(
            'LEVEL1 OPTIONS:',
            dropdown.find('option').length
        );
    }

    $('#globalLevel1').on('change', function(){

        const level1Id = $(this).val();

        if(!level1Id){
            return;
        }

        const level2Children =
            getChildren(level1Id);

        $('#mappingTable tbody tr').each(function(){

            let row = $(this);

            let level2Select =
                row.find('.level2');

            let existingLevel2 =
                row.attr('data-level2');

            let existingLevel3 =
                row.attr('data-level3');

            let existingLevel4 =
                row.attr('data-level4');

            // ALWAYS populate Level2 dropdown
            populateDropdown(
                level2Select,
                level2Children
            );


            // RESTORE EXISTING LEVEL2
            if(
                existingLevel2 &&
                level2Select.find(
                    'option[value="' +
                    existingLevel2 +
                    '"]'
                ).length
            ){
                level2Select.val(
                    existingLevel2
                );


                // RESTORE LEVEL3
                let level3Children =
                    getChildren(
                        existingLevel2
                    );

                populateDropdown(
                    row.find('.level3'),
                    level3Children
                );

                if(
                    existingLevel3 &&
                    row.find(
                        '.level3 option[value="' +
                        existingLevel3 +
                        '"]'
                    ).length
                ){
                    row.find('.level3')
                        .val(existingLevel3);
                }

                // RESTORE LEVEL4

                let level4Children =
                    getChildren(
                        existingLevel3
                    );

                populateDropdown(
                    row.find('.level4'),
                    level4Children
                );

                if(
                    existingLevel4 &&
                    row.find(
                        '.level4 option[value="' +
                        existingLevel4 +
                        '"]'
                    ).length
                ){
                    row.find('.level4')
                        .val(existingLevel4);
                }

                return;
            }


            // AUTO MATCH ONLY IF NO LEVEL2
            let keywords =
                row.data('keywords') || '';

            let bestLevel2 =
                findBestMatch(
                    keywords,
                    getChildren(level1Id)
                );

            if(bestLevel2)
            {
                level2Select.val(bestLevel2.id);

                row.attr(
                    'data-level2',
                    bestLevel2.id
                );

                row.data(
                    'level2',
                    bestLevel2.id
                );

                
                // LEVEL 3 AUTO MATCH
                let level3Children =
                    getChildren(
                        bestLevel2.id
                    );

                populateDropdown(
                    row.find('.level3'),
                    level3Children
                );

                let bestLevel3 =
                    findBestMatch(
                        keywords,
                        level3Children
                    );

                if(bestLevel3)
                {
                    row.find('.level3')
                        .val(bestLevel3.id);

                    row.attr(
                        'data-level3',
                        bestLevel3.id
                    );

                    row.data(
                        'level3',
                        bestLevel3.id
                    );

                    
                    // LEVEL 4 AUTO MATCH
                    let level4Children =
                        getChildren(
                            bestLevel3.id
                        );

                    populateDropdown(
                        row.find('.level4'),
                        level4Children
                    );

                    let bestLevel4 =
                        findBestMatch(
                            keywords,
                            level4Children
                        );

                    if(bestLevel4)
                    {
                        row.find('.level4')
                            .val(bestLevel4.id);

                        row.attr(
                            'data-level4',
                            bestLevel4.id
                        );

                        row.data(
                            'level4',
                            bestLevel4.id
                        );
                    }
                }
            }

        });

    });

    function getChildren(parentId)
    {
        return hierarchyLookup[parentId] || [];
    }
    function findBestMatch(
        keywords,
        candidates
    )
    {
        let bestMatch = null;
        let bestScore = 0;

        keywords =
            (keywords || '')
            .toLowerCase();

        candidates.forEach(item => {

            let score = 0;

            let searchText =
                (
                    item.c_asset_name +
                    ',' +
                    (item.c_keywords || '')
                )
                .toLowerCase();

            let words =
                searchText
                .split(',');

            words.forEach(word => {

                word = word.trim();

                if(
                    word &&
                    keywords.includes(word)
                ){
                    score++;
                }
            });

            if(score > bestScore)
            {
                bestScore = score;
                bestMatch = item;
            }

        });

        return bestMatch;
    }
    function populateDropdown(select, children)
    {
        select.empty();

        select.append(`
            <option value="">
                Select
            </option>
        `);

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

    $(document).on(
        'click',
        '.btnAttributes',
        function()
        {
            let row =
                $(this).closest('tr');

            let asset =
                hierarchyData.find(
                    x => x.id === row.data('id')
                );

            if(!asset){
                return;
            }

            buildAttributeTable(asset);

            $('#attributeModal').modal('show');
        }
    );

    async function saveBatch(batch)
    {
        return $.ajax({
            url: window.routes.saveMapping,
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add CSRF header
            },
            data: JSON.stringify({
                //_token: '{{ csrf_token() }}',
                _token: $('meta[name="csrf-token"]').attr('content'),
                mappings: batch
            })
        });
    }

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

        //mappings = mappings.slice(0, 20);

        console.log(
            'Mappings Count:',
            mappings.length
        );

        (async function(){

            try {

                const batchSize = 20;

                for(
                    let i = 0;
                    i < mappings.length;
                    i += batchSize
                )
                {
                    const batch =
                        mappings.slice(
                            i,
                            i + batchSize
                        );

                    console.log(
                        'Saving batch:',
                        (i / batchSize) + 1,
                        'Rows:',
                        batch.length
                    );

                    await saveBatch(batch);
                }

                alert(
                    'Mapping saved successfully.'
                );

                let selectedLevel1 =
                    $('#globalLevel1').val();

                loadHierarchy();

                window.selectedLevel1AfterSave =
                    selectedLevel1;

            }
            catch(error)
            {
                console.error(error);

                alert(
                    'Save failed.'
                );
            }
            finally
            {
                button.prop(
                    'disabled',
                    false
                );

                button.html(`
                    <i class="bi bi-save"></i>
                    Save Mapping
                `);
            }

        })();

    });

    function buildAttributeTable(asset)
    {
        let tbody = $('#attributeTableBody');

        tbody.empty();

        const hiddenFields = [
            'id',
            'dateCreated',
            'dateModified',
            'createdBy',
            'modifiedBy',
            'c_keywords',
            'c_import_batch',
            'c_level1_id',
            'c_level2_id',
            'c_level3_id',
            'c_level4_id',
            'c_matched_level1_id',
            'c_matched_level2_id',
            'c_matched_level3_id',
            'c_matched_level4_id'
        ];

        // Fields shown for all elements
        const commonAttributes = [
            'c_asset_name',
            'c_asset_code',
            'c_sub_asset_name',
            'c_sub_asset_code',
            'c_element_id',
            'c_door_style',
            'c_door_shape',
            'c_direction_of_swing',
            'c_material_code',
            'c_material',
            'c_material_type_code'
        ];

        const elementAttributes = {
            
            // TECH HARDWARE
            'Control Cabinet': [
                'c_level',
                'c_height',
                'c_width',
                'c_depth',
                'c_weight_capacity',
                'c_frame_material',
                'c_finish_material',
                'c_ventilation_material',
                'c_cooling_ventilation',
                'c_load_distribution'
            ],

            'Server Rack': [
                'c_level',
                'c_height',
                'c_width',
                'c_depth',
                'c_weight_capacity',
                'c_frame_material',
                'c_finish_material',
                'c_ventilation_material',
                'c_cooling_ventilation',
                'c_load_distribution'
            ],

            'Cable Trunk': [
                'c_level',
                'c_material',
                'c_mounting',
                'c_weight_capacity'
            ],

            
            // ARCHITECTURE
            'Door': [
                'c_height',
                'c_width',
                'c_thickness',
                'c_frame_material',
                'c_finish_material',
                'c_fire_code_compliance',
                'c_level',
                'c_material',
                'c_door_style',
                'c_door_shape',
                'c_direction_of_swing'
            ],

            'Windows': [
                'c_height',
                'c_width',
                'c_thickness',
                'c_frame_material',
                'c_glass_type',
                'c_tint_type',
                'c_window_style',
                'c_coatings',
                'c_transparency',
                'c_light_transmission',
                'c_thermal_insulation'
            ],

            'Furniture': [
                'c_type',
                'c_material',
                'c_height',
                'c_width',
                'c_length',
                'c_finish_material'
            ],

            'Glass Fence': [
                'c_glass_type',
                'c_height',
                'c_width',
                'c_thickness',
                'c_frame_material',
                'c_mounting',
                'c_gap_between_panels',
                'c_coatings',
                'c_transparency'
            ],

            'Glass Panel': [
                'c_glass_type',
                'c_height',
                'c_width',
                'c_thickness',
                'c_coatings',
                'c_pattern_or_texture',
                'c_tint_type',
                'c_transparency',
                'c_light_transmission',
                'c_thermal_insulation',
                'c_frame_material',
                'c_mounting'
            ],

            'Glass Roof': [
                'c_glass_type',
                'c_length',
                'c_width',
                'c_thickness',
                'c_pattern_or_texture',
                'c_tint_type',
                'c_transparency',
                'c_light_transmission',
                'c_thermal_insulation',
                'c_frame_material',
                'c_mounting'
            ],

            'Grille': [
                'c_type',
                'c_material',
                'c_level',
                'c_height',
                'c_width'
            ],

            'Staircase': [
                'c_type',
                'c_rise',
                'c_run',
                'c_stair_width',
                'c_staircase_height',
                'c_headroom_clearance',
                'c_landings',
                'c_material',
                'c_handrail'
            ],

            'Wall': [
                'c_wall_type',
                'c_floor_level',
                'c_material',
                'c_finish_material',
                'c_insulation',
                'c_height',
                'c_width',
                'c_thickness',
                'c_sound_insulation',
                'c_thermal_insulation',
                'c_fire_resistance'
            ],

            'Roof': [
                'c_roof_style',
                'c_roof_area',
                'c_material',
                'c_underlayment',
                'c_type_of_insulation',
                'c_ventilation_type'
            ],

            
            // HVAC / MEP
            'FANS': [
                'c_airflow_capacity',
                'c_fan_speed',
                'c_power_consumption',
                'c_level',
                'c_noise_level',
                'c_mounting',
                'c_height',
                'c_width',
                'c_material'
            ],

            'HVAC': [
                'c_type',
                'c_control_system',
                'c_airflow_capacity',
                'c_cooling_capacity',
                'c_afue_ratings',
                'c_compressor_type',
                'c_refrigerant_type',
                'c_duct_material',
                'c_noise_level',
                'c_hspf',
                'c_seer',
                'c_filter'
            ],

            'Heat Rejection Fan': [
                'c_airflow_capacity',
                'c_motor_power',
                'c_fan_speed',
                'c_noise_level',
                'c_fan_blades',
                'c_fan_diameter',
                'c_mounting'
            ],

            'Screen Monitor': [
                'c_level',
                'c_screen_size',
                'c_resolution',
                'c_refresh_rate',
                'c_connectivity',
                'c_panel_technology'
            ]
        };


        // Show common fields first

        const displayedFields = new Set();

        commonAttributes.forEach(key => {

            const value = asset[key];

            if (
                value !== null &&
                value !== undefined &&
                value !== '' &&
                value !== 'NULL'
            ) {
                displayedFields.add(key);

                tbody.append(`
                    <tr>
                        <td>${formatLabel(key)}</td>
                        <td>${value}</td>
                    </tr>
                `);
            }

        });

        // Show remaining asset attributes

        Object.entries(asset).forEach(([key, value]) => {

            if (displayedFields.has(key)) {
                return;
            }

            if (hiddenFields.includes(key)) {
                return;
            }

            if (
                !key.startsWith('c_') ||
                value === null ||
                value === undefined ||
                value === '' ||
                value === 'NULL'
            ) {
                return;
            }

            tbody.append(`
                <tr>
                    <td>${formatLabel(key)}</td>
                    <td>${value}</td>
                </tr>
            `);

        });
    }

    function formatLabel(field)
    {
        return field
            .replace('c_', '')
            .replaceAll('_', ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
    }

    function autoMatchAllRows()
    {
        let level1Id =
            $('#globalLevel1').val();

        if(!level1Id){
            return;
        }

        $('#mappingTable tbody tr').each(function(){

            let row = $(this);

            let keywords =
                (row.data('keywords') || '')
                .toLowerCase();

            // LEVEL 2
            let level2Children =
                getChildren(level1Id);

            populateDropdown(
                row.find('.level2'),
                level2Children
            );

            let bestLevel2 =
                findBestMatch(
                    keywords,
                    level2Children
                );

            if(!bestLevel2){
                return;
            }

            row.find('.level2')
                .val(bestLevel2.id);

            row.attr(
                'data-level2',
                bestLevel2.id
            );

            // LEVEL 3
            let level3Children =
                getChildren(bestLevel2.id);

            populateDropdown(
                row.find('.level3'),
                level3Children
            );

            let bestLevel3 =
                findBestMatch(
                    keywords,
                    level3Children
                );

            if(bestLevel3)
            {
                row.find('.level3')
                    .val(bestLevel3.id);

                row.attr(
                    'data-level3',
                    bestLevel3.id
                );

                // LEVEL 4

                let level4Children =
                    getChildren(bestLevel3.id);

                populateDropdown(
                    row.find('.level4'),
                    level4Children
                );

                let bestLevel4 =
                    findBestMatch(
                        keywords,
                        level4Children
                    );

                if(bestLevel4)
                {
                    row.find('.level4')
                        .val(bestLevel4.id);

                    row.attr(
                        'data-level4',
                        bestLevel4.id
                    );
                }
            }
        });
    }

});