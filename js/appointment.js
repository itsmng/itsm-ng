/* global FullCalendar, FullCalendarLocales */
class AppointmentCalendar {
  constructor() {
    this.calendar = null;
    this.options = {};
  }

  display(params) {
    ITSMAppointmentCalendar.options = params || {};
    ITSMAppointmentCalendar.bindTargetPicker();

    const calendarEl = document.getElementById("appointment-calendar");
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
      selectOverlap: function (event) {
        const props = event.extendedProps || {};
        return props.type === "availability" || (props.type === "unavailability" && props.is_available);
      },
      selectAllow: function (info) {
        return ITSMAppointmentCalendar.isSelectionBookable(info);
      },
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
      events: {
        url: ITSMAppointmentCalendar.options.ajax_url,
        type: "POST",
        extraParams: function () {
          return {
            action: "get_events",
            appointmenttargets_id: ITSMAppointmentCalendar.getEffectiveAppointmentTargetId(),
          };
        },
      },
      eventRender: function (info) {
        const props = info.event.extendedProps || {};
        info.el.setAttribute("data-testid", "appointment-calendar-event");
        if (props.type) {
          info.el.setAttribute("data-appointment-event-type", props.type);
        }
        if (props.type === "availability") {
          info.el.setAttribute("data-appointment-availability-id", String(info.event.id).replace("availability-", ""));
        } else if (props.type === "unavailability") {
          info.el.setAttribute("data-appointment-unavailability-id", props.unavailability_id);
        } else {
          info.el.setAttribute("data-appointment-id", info.event.id);
        }
        if (props.comment) {
          info.el.setAttribute("title", props.comment);
        }
      },
      select: function (info) {
        if (!ITSMAppointmentCalendar.options.can_book) {
          ITSMAppointmentCalendar.calendar.unselect();
          return;
        }
        if (!ITSMAppointmentCalendar.isSelectionBookable(info)) {
          ITSMAppointmentCalendar.calendar.unselect();
          return;
        }
        ITSMAppointmentCalendar.openForm({
          appointmenttargets_id: ITSMAppointmentCalendar.getEffectiveAppointmentTargetId(),
          begin: ITSMAppointmentCalendar.formatDate(info.start),
          end: ITSMAppointmentCalendar.formatDate(info.end),
        });
        ITSMAppointmentCalendar.calendar.unselect();
      },
      eventClick: function (info) {
        const props = info.event.extendedProps || {};
        info.jsEvent.preventDefault();
        if (props.type && props.type !== "appointment") {
          return;
        }
        if (props.can_view_details) {
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

    const loadedLocales = typeof FullCalendarLocales !== "undefined" ? Object.keys(FullCalendarLocales) : [];
    if (loadedLocales.length === 1) {
      ITSMAppointmentCalendar.calendar.setOption("locale", loadedLocales[0]);
    }
    ITSMAppointmentCalendar.calendar.render();
    ITSMAppointmentCalendar.bindAppointmentEventClicks(calendarEl);
  }

  bindTargetPicker() {
    const picker = $("#appointment-calendar-target-picker");
    if (picker.length === 0) {
      ITSMAppointmentCalendar.options.selected_appointmenttargets_id = ITSMAppointmentCalendar.options.appointmenttargets_id;
      return;
    }

    ITSMAppointmentCalendar.options.selected_appointmenttargets_id = picker.val();
    picker.off("change.appointmentTargetPicker");
    picker.on("change.appointmentTargetPicker", function () {
      ITSMAppointmentCalendar.options.selected_appointmenttargets_id = $(this).val();
      ITSMAppointmentCalendar.refresh();
    });

    ITSMAppointmentCalendar.enhanceTargetPicker(picker);
  }

  getEffectiveAppointmentTargetId() {
    return ITSMAppointmentCalendar.options.selected_appointmenttargets_id || ITSMAppointmentCalendar.options.appointmenttargets_id;
  }

  enhanceTargetPicker(picker) {
    const initialize = function () {
      if (typeof picker.select2 !== "function") {
        return;
      }

      picker.select2({
        theme: "bootstrap-5",
        width: "resolve",
        placeholder: ITSMAppointmentCalendar.options.technician_placeholder || __("Search a technician"),
        allowClear: false,
        ajax: {
          url: ITSMAppointmentCalendar.options.ajax_url,
          type: "POST",
          dataType: "json",
          delay: 250,
          data: function (params) {
            return {
              action: "get_group_member_targets",
              appointmenttargets_id: ITSMAppointmentCalendar.options.appointmenttargets_id,
              searchText: params.term || "",
              page: params.page || 1,
              page_limit: 50,
            };
          },
          processResults: function (data) {
            return data || { results: [], pagination: { more: false } };
          },
        },
      });
    };

    if (typeof picker.select2 === "function") {
      initialize();
      return;
    }

    $.getScript(ITSMAppointmentCalendar.options.select2_url, initialize);
  }

  displayTargetManager(params) {
    ITSMAppointmentCalendar.options = params || {};

    const calendarEl = document.getElementById("appointment-target-calendar");
    if (!calendarEl || typeof FullCalendar === "undefined") {
      return;
    }

    ITSMAppointmentCalendar.calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ["dayGrid", "interaction", "timeGrid", "list"],
      defaultView: "timeGridWeek",
      defaultDate: ITSMAppointmentCalendar.options.initial_date,
      height: "auto",
      nowIndicator: true,
      selectable: true,
      selectMirror: true,
      selectOverlap: true,
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
        right: "timeGridWeek,timeGridDay,listWeek",
      },
      events: {
        url: ITSMAppointmentCalendar.options.ajax_url,
        type: "POST",
        extraParams: function () {
          return {
            action: "get_target_events",
            appointmenttargets_id: ITSMAppointmentCalendar.options.appointmenttargets_id,
          };
        },
      },
      eventRender: function (info) {
        const props = info.event.extendedProps || {};
        if (props.comment) {
          info.el.setAttribute("title", props.comment);
        }
        if (props.type) {
          info.el.setAttribute("data-appointment-event-type", props.type);
        }
        if (props.type === "availability") {
          info.el.setAttribute("data-appointment-availability-id", String(info.event.id).replace("availability-", ""));
        } else if (props.type === "unavailability") {
          info.el.setAttribute("data-appointment-unavailability-id", props.unavailability_id);
        } else if (props.type === "appointment") {
          info.el.setAttribute("data-appointment-id", props.appointment_id);
        }
      },
      select: function (info) {
        ITSMAppointmentCalendar.openUnavailabilityForm({
          appointmenttargets_id: ITSMAppointmentCalendar.options.appointmenttargets_id,
          begin: ITSMAppointmentCalendar.formatDate(info.start),
          end: ITSMAppointmentCalendar.formatDate(info.end),
          is_available: 0,
        });
        ITSMAppointmentCalendar.calendar.unselect();
      },
      eventClick: function (info) {
        const props = info.event.extendedProps || {};
        info.jsEvent.preventDefault();
        if (props.type === "unavailability" && props.can_edit) {
          ITSMAppointmentCalendar.openUnavailabilityForm({ id: props.unavailability_id });
        } else if (props.type === "appointment" && props.can_view_details) {
          ITSMAppointmentCalendar.openForm({ id: props.appointment_id });
        }
      },
      eventDrop: function (info) {
        ITSMAppointmentCalendar.updateUnavailabilityTimes(info);
      },
      eventResize: function (info) {
        ITSMAppointmentCalendar.updateUnavailabilityTimes(info);
      },
    });

    const loadedLocales = typeof FullCalendarLocales !== "undefined" ? Object.keys(FullCalendarLocales) : [];
    if (loadedLocales.length === 1) {
      ITSMAppointmentCalendar.calendar.setOption("locale", loadedLocales[0]);
    }
    ITSMAppointmentCalendar.calendar.render();
    ITSMAppointmentCalendar.bindAppointmentEventClicks(calendarEl);
  }

  bindAppointmentEventClicks(calendarEl) {
    if (calendarEl._appointmentEventClickHandler) {
      calendarEl.removeEventListener("click", calendarEl._appointmentEventClickHandler, true);
    }

    calendarEl._appointmentEventClickHandler = function (event) {
      if (!(event.target instanceof Element)) {
        return;
      }

      const target = event.target.closest("[data-appointment-id]");
      if (!target || !calendarEl.contains(target)) {
        return;
      }

      const appointmentId = String(target.getAttribute("data-appointment-id") || "");
      if (appointmentId === "") {
        return;
      }

      const calendarEvent = ITSMAppointmentCalendar.calendar
        ? ITSMAppointmentCalendar.calendar.getEventById(appointmentId)
        : null;
      const props = calendarEvent ? calendarEvent.extendedProps || {} : {};
      if (calendarEvent && props.type && props.type !== "appointment") {
        return;
      }
      if (calendarEvent && props.can_view_details === false) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      ITSMAppointmentCalendar.openForm({ id: appointmentId });
    };

    calendarEl.addEventListener("click", calendarEl._appointmentEventClickHandler, true);
  }

  bindTargetSearch() {
    const search = $("#appointment-target-search");
    const table = $("#appointment-target-table");
    if (search.length === 0 || table.length === 0) {
      return;
    }

    const rows = table.find("[data-appointment-target-row]");
    const emptyRow = table.find(".appointment-target-list__empty");

    const filterRows = function () {
      const query = String(search.val() || "").trim().toLocaleLowerCase();
      let visibleCount = 0;

      rows.each(function () {
        const row = $(this);
        const matches = query === "" || String(row.data("search") || "").toLocaleLowerCase().indexOf(query) !== -1;
        row.toggle(matches);
        if (matches) {
          visibleCount++;
        }
      });

      if (emptyRow.length > 0) {
        emptyRow.toggle(visibleCount === 0);
      }
    };

    search.off("input.appointmentTargetSearch keyup.appointmentTargetSearch search.appointmentTargetSearch");
    search.on("input.appointmentTargetSearch keyup.appointmentTargetSearch search.appointmentTargetSearch", filterRows);
    filterRows();
  }

  openForm(params) {
    const dialog = $("<div class='appointment-calendar-dialog'></div>");
    const data = Object.assign({ action: "get_form" }, params || {});
    const title = ITSMAppointmentCalendar.options.appointment_title || __("Appointment");

    dialog.dialog({
      modal: true,
      width: Math.min($(window).width() - 40, 760),
      maxHeight: $(window).height() - 80,
      title: title,
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
  }

  bindForm(dialog) {
    let clickedButton = null;

    dialog.find("button, input[type=submit]").on("click", function () {
      clickedButton = this;
    });

    dialog.find("form").on("submit", function (event) {
      event.preventDefault();

      const form = $(this);
      const data = form.serializeArray();
      data.push({ name: "action", value: "save" });

      if (clickedButton && clickedButton.name) {
        data.push({ name: clickedButton.name, value: clickedButton.value || "1" });
      } else {
        const defaultButton = form.find("button[name=add], input[name=add], button[name=update], input[name=update]").first();
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
  }

  openUnavailabilityForm(params) {
    const dialog = $("<div class='appointment-calendar-dialog'></div>");
    const data = Object.assign({ action: "get_unavailability_form" }, params || {});
    const title = ITSMAppointmentCalendar.options.unavailability_title || __("Unavailability");

    dialog.dialog({
      modal: true,
      width: Math.min($(window).width() - 40, 680),
      maxHeight: $(window).height() - 80,
      title: title,
      open: function () {
        dialog.load(ITSMAppointmentCalendar.options.ajax_url, data, function () {
          ITSMAppointmentCalendar.bindUnavailabilityForm(dialog);
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
  }

  bindUnavailabilityForm(dialog) {
    let clickedButton = null;

    dialog.find("button, input[type=submit]").on("click", function () {
      clickedButton = this;
    });

    dialog.find("form").on("submit", function (event) {
      event.preventDefault();

      const form = $(this);
      const data = form.serializeArray();
      data.push({ name: "action", value: "save_unavailability" });

      if (clickedButton && clickedButton.name) {
        data.push({ name: clickedButton.name, value: clickedButton.value || "1" });
      } else {
        const defaultButton = form.find("button[name=add], input[name=add], button[name=update], input[name=update]").first();
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
  }

  refresh() {
    if (ITSMAppointmentCalendar.calendar) {
      ITSMAppointmentCalendar.calendar.refetchEvents();
    }
  }

  updateEventTimes(info) {
    const event = info.event;

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
            title: ITSMAppointmentCalendar.options.appointment_title || __("Appointment"),
          });
        }
      },
      error: function () {
        info.revert();
      },
    });
  }

  updateUnavailabilityTimes(info) {
    const event = info.event;
    const props = event.extendedProps || {};

    if (props.type !== "unavailability" || !props.can_edit) {
      info.revert();
      return;
    }

    $.ajax({
      url: ITSMAppointmentCalendar.options.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "update_unavailability_times",
        id: props.unavailability_id,
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
            title: ITSMAppointmentCalendar.options.unavailability_title || __("Unavailability"),
          });
        }
      },
      error: function () {
        info.revert();
      },
    });
  }

  isSelectionBookable(info) {
    if (!ITSMAppointmentCalendar.options.can_book) {
      return false;
    }

    const start = info.start;
    const end = info.end;
    if (!(start instanceof Date) || !(end instanceof Date) || start >= end) {
      return false;
    }

    if (start.toDateString() !== end.toDateString()) {
      return false;
    }

    let coveredByAvailability = false;
    let coveredByOpeningUnavailability = false;

    for (const event of ITSMAppointmentCalendar.calendar.getEvents()) {
      const props = event.extendedProps || {};

      if (props.type === "appointment" && ITSMAppointmentCalendar.eventsOverlap(start, end, event.start, event.end)) {
        return false;
      }

      if (props.type !== "unavailability" || !ITSMAppointmentCalendar.eventsOverlap(start, end, event.start, event.end)) {
        continue;
      }

      if (!props.is_available) {
        return false;
      }

      if (props.is_available && ITSMAppointmentCalendar.eventCovers(start, end, event.start, event.end)) {
        coveredByOpeningUnavailability = true;
      }
    }

    if (coveredByOpeningUnavailability) {
      return true;
    }

    const selectionDay = start.getDay();
    const selectionStart = ITSMAppointmentCalendar.secondsSinceMidnight(start);
    const selectionEnd = ITSMAppointmentCalendar.secondsSinceMidnight(end);

    for (const event of ITSMAppointmentCalendar.calendar.getEvents()) {
      const props = event.extendedProps || {};
      if (props.type !== "availability" || Number(props.day) !== selectionDay) {
        continue;
      }

      if (ITSMAppointmentCalendar.eventCovers(start, end, event.start, event.end)) {
        coveredByAvailability = true;
        break;
      }

      const availabilityStart = ITSMAppointmentCalendar.timeToSeconds(props.begin);
      const availabilityEnd = ITSMAppointmentCalendar.timeToSeconds(props.end);
      if (availabilityStart <= selectionStart && availabilityEnd >= selectionEnd) {
        coveredByAvailability = true;
        break;
      }
    }

    return coveredByAvailability;
  }

  eventCovers(start, end, eventStart, eventEnd) {
    if (!(eventStart instanceof Date) || !(eventEnd instanceof Date)) {
      return false;
    }

    return eventStart <= start && eventEnd >= end;
  }

  eventsOverlap(start, end, eventStart, eventEnd) {
    if (!(eventStart instanceof Date)) {
      return false;
    }

    const normalizedEventEnd = eventEnd instanceof Date ? eventEnd : eventStart;
    return normalizedEventEnd > start && eventStart < end;
  }

  secondsSinceMidnight(date) {
    return date.getHours() * 3600 + date.getMinutes() * 60 + date.getSeconds();
  }

  timeToSeconds(value) {
    const parts = String(value || "00:00:00").split(":").map(function (part) {
      return parseInt(part, 10) || 0;
    });

    return (parts[0] || 0) * 3600 + (parts[1] || 0) * 60 + (parts[2] || 0);
  }

  formatDate(date) {
    const pad = function (value) {
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
  }
}

const ITSMAppointmentCalendar = new AppointmentCalendar();
window.ITSMAppointmentCalendar = ITSMAppointmentCalendar;
