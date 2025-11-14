$(document).ready(function () {
    const projectDropdown = $('#project_id');
    const layerDropdown = $('#data_id');
    const layerWrapper = $('#layer-wrapper');

    // Load Projects (First Dropdown)
    $.ajax({
        url: window.uploadToolConfig.api.getProjects,
        type: 'GET',
        success: function (response) {
            if (response.status === 'success') {
                projectDropdown.html('<option value="">Select Project...</option>');
                response.projects.forEach(project => {
                    projectDropdown.append(`
                        <option value="${project.project_id_number}">
                            ${project.project_name} (${project.project_id})
                        </option>
                    `);
                });
            }
        },
        error: function () {
            projectDropdown.html('<option disabled>Error loading projects</option>');
        }
    });

    // Load Layers (Second Dropdown) when project changes
    projectDropdown.on('change', function () {
        const projectId = $(this).val();
        if (!projectId) {
            layerWrapper.hide();
            layerDropdown.html('<option value="">Select Layer...</option>');
            return;
        }

        $.ajax({
            url: `${window.uploadToolConfig.api.getLayersByProject}/${projectId}`,
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    if (response.layers.length === 0) {
                        layerDropdown.html('<option value="">No layers found for this project</option>');
                    } else {
                        layerDropdown.html('<option value="">Select Layer...</option>');
                        response.layers.forEach(layer => {
                            layerDropdown.append(`
                                <option value="${layer.Data_ID}">
                                    ${layer.Layer_Name} (${layer.Data_ID})
                                </option>
                            `);
                        });
                    }
                    layerWrapper.show();
                }
            },
            error: function () {
                layerDropdown.html('<option disabled>Error loading layers</option>');
                layerWrapper.show();
            }
        });
    });

    $('#project_id, #data_id').select2({
        placeholder: 'Search...',
        allowClear: true,
        width: '100%'
    });
});
