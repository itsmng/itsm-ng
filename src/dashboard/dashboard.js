var dataPreview = [];
var filters = {};

function fetchPreview() {
  if (!$("#data-selection-search-content form").length) {
    return;
  }
  const formData = new FormData($("#data-selection-search-content form")[0]);
  const formContent = {};
  for (const [key, value] of formData.entries()) {
    if (key.startsWith("criteria")) {
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
  const jsonData = JSON.stringify(formContent);

  $.ajax({
    url: "./dashboard.ajax.php",
    type: "POST",
    data: {
      action: "preview",
      dataFilters: jsonData,
    },
    success: function (data) {
      const dataToPreview = JSON.parse(data);
      const cols = dataToPreview.data.cols;

      const orderBySelect = document.getElementById("parameter-selection-widget-modal-select");
      orderBySelect.innerHTML = "";
      for (const col of cols) {
        const option = document.createElement("option");
        option.value = col.id;
        var name = col.name;
        if (col.groupname) {
          name += " (" + col.groupname.name + ")";
        }
        option.innerHTML = name;
        orderBySelect.appendChild(option);
      }
      dataPreview = dataToPreview.data.rows;
      filters = formContent;
      updatePreview();
    },
    error: function (data) {
      console.error(data);
    },
  });
}

function makeCount() {
  const icon = $("#icon-widget-modal").val();
  const title = $("#title-widget-modal").val();
  const value = dataPreview.length;

  $("#preview-graph-widget-modal").html(`
        <div class="d-flex justify-content-center align-items-center">
            <i class="${icon} fs-1 text-{{color}}" aria-hidden='true'></i>
            <div class="ms-3">
                <div class="fw-bold text-wrap">${title}</div>
                <div class="fs-3">${value}</div>
            </div>
        </div>
    `);
}

function updatePreview() {
  const labels = dataPreview.map((row) => {
    return row[$("#ItemTypeDropdownForDashboard").val() + "_" + $("#parameter-selection-widget-modal-select").val()][0].name;
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
  };
  const params = {
    axisY: {
      onlyInteger: true,
    },
  };
  if (format == "count") {
    makeCount();
  } else {
    makeChart(format, data, params);
  }
}

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
    url: "./dashboard.ajax.php",
    type: "POST",
    data: {
      id: $("input[name='dashboardId']").val(),
      action: "delete",
      coords: JSON.stringify([x, y]),
    },
    success: function (data) {
      location.reload();
    },
    error: function (data) {
      console.error(data);
    },
  });
}

function addWidget() {
  const coords = JSON.parse($('#widgetModal input[name="coords"]').val());
  const title = $("#title-widget-modal").val();
  const format = $('input[name="format"]:checked').val();

  const options = {
    icon: $("#icon-widget-modal").val(),
    direction: $("#direction-selection-widget-modal-select").val(),
    pieFormat: $("#pie-format-selection-widget-modal-select").val(),
  };

  const widget = { format, coords, title, filters, options };

  widget.options.comparison = $("#parameter-selection-widget-modal-select").val();
  $.ajax({
    url: "./dashboard.ajax.php",
    type: "POST",
    data: {
      id: $("input[name='dashboardId']").val(),
      action: "add",
      widget: JSON.stringify(widget),
    },
    success: function (data) {
      location.reload();
    },
    error: function (data) {
      console.error(data);
    },
  });
}

function changeType() {
  const params = $('input[name="format"]:checked').parent().data("params");

  const direction = $('input[name="format"]:checked').parent().data("direction");
  const pieFormat = $('input[name="format"]:checked').parent().data("pieformat");
  const comparison = $("#parameter-selection-widget-modal-select");

  const directionSelect = $("#direction-selection-widget-modal-select");
  const pieFormatSelect = $("#pie-format-selection-widget-modal-select");

  const icon = $("#icon-widget-modal");

  icon.parent().addClass("d-none");
  comparison.parent().addClass("d-none");
  directionSelect.parent().addClass("d-none");
  pieFormatSelect.parent().addClass("d-none");

  if (params > 1) {
    comparison.parent().removeClass("d-none");
  } else {
    icon.parent().removeClass("d-none");
  }

  if (direction) {
    directionSelect.parent().removeClass("d-none");
  } else {
    directionSelect.parent().addClass("d-none");
  }

  if (pieFormat) {
    pieFormatSelect.parent().removeClass("d-none");
  } else {
    pieFormatSelect.parent().addClass("d-none");
  }
  updatePreview();
}
