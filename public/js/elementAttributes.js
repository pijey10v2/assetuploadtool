const elementAttributes = {
            
    // TECH HARDWARE
    'Control Cabinet': [
        //'c_level',
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
        //'c_level',
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
        //'c_level',
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
        'c_surface_finish',
        'c_fire_code_compliance',
        //'c_level',
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
        'c_finish'
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
        //'c_level',
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
        //'c_level',
        'c_noise_level',
        'c_mounting',
        'c_height',
        'c_width',
        'c_material'
    ],

    'HVAC': [
        'c_system_type',
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
        //'c_level',
        'c_screen_size',
        'c_resolution',
        'c_refresh_rate',
        'c_connectivity',
        'c_panel_technology'
    ],

    //PRO_DC_Structure
    'Beam': [
        //'c_element_id',
        'c_volume',
        'c_surface_area',
        //'c_asset_name',
        //'c_asset_id',
        'c_asset_category',
        'c_asset_type',
        'c_beam_material',
        'c_parent_asset',
        'c_building_name',
        //'c_level',
        //'c_zone',
        'c_beam_position',
        'c_beam_type',
        'c_material_grade',
        'c_reinforcement'
    ],

    'Ceiling': [
        //'c_element_id',
        'c_item',
        'c_element_description',
        //'c_level',
        'c_weight',
        'c_transparency',
        'c_distance',
        'c_angle',
        'c_capped',
        'c_rotation_x',
        'c_rotation_y',
        'c_rotation_z'
    ],

    'Column': [
        //'c_element_id',
        //'c_level',
        'c_color',
        'c_weight',
        'c_transparency',
        'c_surface_area',
        'c_thickness',
        //'c_asset_name',
        //'c_asset_id',
        'c_asset_category',
        'c_asset_type',
        'c_structural_system',
        'c_building_name',
        //'c_zone',
        'c_column_section_type',
        'c_section_size',
        'c_steel_grade',
        'c_connection_type'
    ],

    'Floor': [
        //'c_element_id',
        'c_item',
        'c_element_description',
        //'c_level',
        'c_color',
        'c_transparency',
        'c_rotation_x',
        'c_rotation_y',
        'c_rotation_z',
        'c_volume',
        'c_surface_area',
        //'c_asset_name',
        //'c_asset_id',
        'c_asset_category',
        'c_asset_type',
        'c_structural_system',
        'c_parent_asset',
        'c_building_name',
        'c_floor_level',
        //'c_zone',
        'c_slab_area',
        'c_slab_type',
        'c_thickness',
        'c_concrete_grade',
        'c_reinforcement_type',
        'c_construction_method'
    ],

    'Pile Cap': [
        //'c_element_id',
        //'c_level',
        'c_volume',
        'c_surface_area',
        //'c_asset_name',
        //'c_asset_id',
        'c_asset_category',
        'c_asset_type',
        'c_associated_structure',
        'c_building_name',
        //'c_zone',
        'c_pile_cap_type',
        'c_dimensions',
        'c_concrete_grade',
        'c_reinforcement_type'
    ],

    'Pile': [
        //'c_element_id',
        //'c_asset_name',
        //'c_asset_id',
        'c_asset_category',
        'c_asset_type',
        'c_parent_asset',
        'c_building_name',
        //'c_zone',
        'c_depth_below_ground_level',
        'c_pile_head_elevation',
        'c_pile_type',
        'c_pile_material',
        'c_pile_diameter',
        'c_pile_length',
        'c_concrete_grade',
        'c_reinforcement_type',
        'c_soil_type'
    ],
};

window.elementAttributes = elementAttributes;