var dataPreview = [];

$(function() {
    $('#widgetModal').insertAfter($('body'));
    $('#data-selection-widget-modal #data-selection-search-content form').attr('action', "#");
});

function fetchPreview() {
    if (!$('#data-selection-search-content form').length) {
        return;
    }
    const formData = new FormData($('#data-selection-search-content form')[0]);
    const formContent = {};
    for (const [key, value] of formData.entries()) {
        if (key.startsWith('criteria')) {
            const matches = key.match(/\[(\d+)\]\[(\w+)\]/);
            if (matches && matches.length === 3) {
                const index = matches[1];
                const property = matches[2];
                if (!formContent.criteria) {
                    formContent.criteria = [];
                }
                if (!formContent.criteria[index]) {
                    formContent.criteria[index] = {};
                }
                formContent.criteria[index][property] = value;
            }
        } else {
            formContent[key] = value;
        }
    }
    console.log(formContent);
    const jsonData = JSON.stringify(formContent);
        
    $.ajax({
        url: "./dashboard.ajax.php",
        type: "POST",
        data: {
            action: 'preview',
            dataFilters: jsonData,
        },
        success: function(data) {
            const dataToPreview = JSON.parse(data);
            console.log(dataToPreview);
            const cols = dataToPreview.data.cols;
            
            const orderBySelect = document.getElementById('parameter-selection-widget-modal-select');
            for (const col of cols) {
                const option = document.createElement('option');
                option.value = col.id;
                option.innerHTML = col.name;
                orderBySelect.appendChild(option);
            }
            dataPreview = dataToPreview.data.rows;
            updatePreview();
        },
        error: function(data) {
            console.error(data);
        }
    });
}

function updatePreview() {
    
    const labels = dataPreview.map(row => {
        return row[$('#ItemTypeDropdownForDashboard').val() + '_' + $('#parameter-selection-widget-modal-select').val()][0].name;
    });
    const series = {};
    for (const label of labels) {
        if (series[label]) {
            series[label]++;
        } else {
            series[label] = 1;
        }
    }
    const uniqueLabels = [...new Set(labels)];
    const seriesCounts = Object.values(series);

    const format = $('input[name="format"]:checked').val();
    const data = {
        labels: uniqueLabels,
        series: [seriesCounts],
    }
    const params = {
        axisY: {
            onlyInteger: true,
        },
    }
    $('#preview-graph-widget-modal').html('');
    switch (format) {
        case 'bar':
            params.horizontalBars = $('#direction-selection-widget-modal-select').val() == 'horizontal';
            new Chartist.BarChart('#preview-graph-widget-modal', data, params);
            break;
        case 'line':
            new Chartist.LineChart('#preview-graph-widget-modal', data, params);
            break;
        case 'pie':
            data.series = seriesCounts;
            params.donutWidth = 120;
            params.donut = $('#pie-format-selection-widget-modal-select').val() == 'donut'
                || $('#pie-format-selection-widget-modal-select').val() == "half";
                if ($('#pie-format-selection-widget-modal-select').val() == "half") {
                    params.startAngle = 270;
                    params.total = seriesCounts.reduce((a, b) => a + b, 0) * 2;
            }

            new Chartist.PieChart('#preview-graph-widget-modal', data, params);
            break;
        case 'count':
            $('#preview-graph-widget-modal').html(dataPreview.length);
    }
}

$("#data-selection-search-content").on('change', fetchPreview);
$("#parameter-selection-widget-modal-select").on('change', updatePreview);
$('#direction-selection-widget-modal-select').on('change', updatePreview);
$('#pie-format-selection-widget-modal-select').on('change', updatePreview);

function openWidgetModal(coords) {
    $('#widgetModal input[name="coords"]').val(JSON.stringify(coords));
    $("#widgetModal").modal("show");
}

function toggleEdit() {
    // toggle display of all edit button
    var editButtons = document.getElementsByClassName("editButton");
    for (var i = 0; i < editButtons.length; i++) {
        editButtons[i].style.display = editButtons[i].style.display == "none" ? "block" : "none";
    }
}

function removeWidget(x, y) {
    $.ajax({
        url: "{{ajaxUrl}}",
        type: "POST",
        data: {
            id: "{{dashboardId}}",
            action: "delete",
            coords: JSON.stringify([x, y]),
        },
        success: function(data) {
            location.reload();
        },
        error: function(data) {
            console.error(data);
        }
    });
}

function addWidget() {
    const coords = JSON.parse($('#widgetModal input[name="coords"]').val());
}

function changeType() {
    const params = $('input[name="format"]:checked').parent().data('params');

    const direction = $('input[name="format"]:checked').parent().data('direction');
    const pieFormat = $('input[name="format"]:checked').parent().data('pieformat');
    const comparison = $('#parameter-selection-widget-modal-select');

    
    const directionSelect = $('#direction-selection-widget-modal-select');
    const pieFormatSelect = $('#pie-format-selection-widget-modal-select');
    
    const icon = $('#icon-widget-modal');

    icon.parent().addClass('d-none');
    comparison.parent().addClass('d-none');
    directionSelect.parent().addClass('d-none');
    pieFormatSelect.parent().addClass('d-none');

    if (params > 1) {
        comparison.parent().removeClass('d-none');
    } else {
        icon.parent().removeClass('d-none');
    }

    if (direction) {
        directionSelect.parent().removeClass('d-none');
    } else {
        directionSelect.parent().addClass('d-none');
    }

    if (pieFormat) {
        pieFormatSelect.parent().removeClass('d-none');
    } else {
        pieFormatSelect.parent().addClass('d-none');
    }
    updatePreview();
}
