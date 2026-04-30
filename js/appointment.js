/* global FullCalendar */
var ITSMAppointmentCalendar = {
  calendar: null,
  options: {},

  display: function (params) {
    ITSMAppointmentCalendar.options = params || {};

    var calendarEl = document.getElementById("appointment-calendar");
    if (!calendarEl || typeof FullCalendar === "undefined") {
      return;
    }

    ITSMAppointmentCalendar.calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ["dayGrid", "interaction", "timeGrid", "list"],
      defaultView: ITSMAppointmentCalendar.options.can_book ? "timeGridWeek" : "dayGridMonth",
      defaultDate: ITSMAppointmentCalendar.options.initial_date,
      height: "auto",
      nowIndicator: true,
      selectable: ITSMAppointmentCalendar.options.can_book,
      selectMirror: true,
      selectOverlap: false,
      editable: true,
      eventDurationEditable: true,
      allDaySlot: false,
      minTime: ITSMAppointmentCalendar.options.planning_begin || "08:00:00",
      maxTime: ITSMAppointmentCalendar.options.planning_end || "20:00:00",
      slotDuration: "00:30:00",
      snapDuration: "00:15:00",
      header: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
      },
      buttonText: {
        today: __("Today"),
        month: __("Month"),
        week: __("Week"),
        day: __("Day"),
        list: __("List"),
      },
      events: {
        url: ITSMAppointmentCalendar.options.ajax_url,
        type: "POST",
        extraParams: function () {
          return {
            action: "get_events",
            appointmenttargets_id: ITSMAppointmentCalendar.options.appointmenttargets_id,
          };
        },
      },
      eventRender: function (info) {
        var props = info.event.extendedProps || {};
        info.el.setAttribute("data-testid", "appointment-calendar-event");
        info.el.setAttribute("data-appointment-id", info.event.id);
        if (props.comment) {
          info.el.setAttribute("title", props.comment);
        }
      },
      select: function (info) {
        if (!ITSMAppointmentCalendar.options.can_book) {
          ITSMAppointmentCalendar.calendar.unselect();
          return;
        }
        ITSMAppointmentCalendar.openForm({
          appointmenttargets_id: ITSMAppointmentCalendar.options.appointmenttargets_id,
          begin: ITSMAppointmentCalendar.formatDate(info.start),
          end: ITSMAppointmentCalendar.formatDate(info.end),
        });
        ITSMAppointmentCalendar.calendar.unselect();
      },
      eventClick: function (info) {
        var props = info.event.extendedProps || {};
        info.jsEvent.preventDefault();
        if (props.can_edit) {
          ITSMAppointmentCalendar.openForm({ id: info.event.id });
        }
      },
      eventDrop: function (info) {
        ITSMAppointmentCalendar.updateEventTimes(info);
      },
      eventResize: function (info) {
        ITSMAppointmentCalendar.updateEventTimes(info);
      },
    });

    var loadedLocales = typeof FullCalendarLocales !== "undefined" ? Object.keys(FullCalendarLocales) : [];
    if (loadedLocales.length === 1) {
      ITSMAppointmentCalendar.calendar.setOption("locale", loadedLocales[0]);
    }
    ITSMAppointmentCalendar.calendar.render();
  },

  openForm: function (params) {
    var dialog = $("<div class='appointment-calendar-dialog'></div>");
    var data = Object.assign({ action: "get_form" }, params || {});

    dialog.dialog({
      modal: true,
      width: Math.min($(window).width() - 40, 760),
      maxHeight: $(window).height() - 80,
      title: __("Appointment"),
      open: function () {
        dialog.load(ITSMAppointmentCalendar.options.ajax_url, data, function () {
          ITSMAppointmentCalendar.bindForm(dialog);
          dialog.dialog("option", "position", {
            my: "center",
            at: "center",
            of: window,
            collision: "fit",
          });
        });
      },
      close: function () {
        dialog.dialog("destroy").remove();
      },
    });
  },

  bindForm: function (dialog) {
    var clickedButton = null;

    dialog.find("button, input[type=submit]").on("click", function () {
      clickedButton = this;
    });

    dialog.find("form").on("submit", function (event) {
      event.preventDefault();

      var form = $(this);
      var data = form.serializeArray();
      data.push({ name: "action", value: "save" });

      if (clickedButton && clickedButton.name) {
        data.push({ name: clickedButton.name, value: clickedButton.value || "1" });
      } else {
        var defaultButton = form.find("button[name=add], input[name=add], button[name=update], input[name=update]").first();
        if (defaultButton.length > 0) {
          data.push({ name: defaultButton.attr("name"), value: defaultButton.val() || "1" });
        }
      }

      $.ajax({
        url: ITSMAppointmentCalendar.options.ajax_url,
        type: "POST",
        dataType: "json",
        data: $.param(data),
        success: function (response) {
          if (response && response.success) {
            dialog.dialog("close");
            ITSMAppointmentCalendar.refresh();
            window.displayAjaxMessageAfterRedirect();
            return;
          }
          if (response && response.html) {
            dialog.find(".appointment-calendar-form-error").remove();
            dialog.prepend("<div class='appointment-calendar-form-error'>" + response.html + "</div>");
          }
        },
      });
    });
  },

  refresh: function () {
    if (ITSMAppointmentCalendar.calendar) {
      ITSMAppointmentCalendar.calendar.refetchEvents();
    }
  },

  updateEventTimes: function (info) {
    var event = info.event;

    $.ajax({
      url: ITSMAppointmentCalendar.options.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "update_times",
        id: event.id,
        begin: ITSMAppointmentCalendar.formatDate(event.start),
        end: ITSMAppointmentCalendar.formatDate(event.end),
      },
      success: function (response) {
        if (response && response.success) {
          ITSMAppointmentCalendar.refresh();
          return;
        }
        info.revert();
        if (response && response.html) {
          $("<div></div>").html(response.html).dialog({
            modal: true,
            width: "auto",
            title: __("Appointment"),
          });
        }
      },
      error: function () {
        info.revert();
      },
    });
  },

  formatDate: function (date) {
    var pad = function (value) {
      return value < 10 ? "0" + value : value;
    };

    return (
      date.getFullYear() +
      "-" +
      pad(date.getMonth() + 1) +
      "-" +
      pad(date.getDate()) +
      " " +
      pad(date.getHours()) +
      ":" +
      pad(date.getMinutes()) +
      ":00"
    );
  },
};
