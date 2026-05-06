/* global FullCalendar, FullCalendarLocales */
class ReservationCalendar {
  constructor() {
    this.calendar = null;
    this.options = {};
  }

  display(params) {
    ITSMReservationCalendar.options = params || {};

    const calendarEl = document.getElementById("reservation-calendar");
    if (!calendarEl || typeof FullCalendar === "undefined") {
      return;
    }

    ITSMReservationCalendar.calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ["dayGrid", "interaction", "timeGrid", "list"],
      defaultView: ITSMReservationCalendar.options.can_reserve ? "timeGridWeek" : "dayGridMonth",
      defaultDate: ITSMReservationCalendar.options.initial_date,
      height: "auto",
      nowIndicator: true,
      selectable: ITSMReservationCalendar.options.can_reserve,
      selectMirror: true,
      selectOverlap: false,
      eventOverlap: true,
      editable: true,
      eventDurationEditable: true,
      allDaySlot: false,
      minTime: ITSMReservationCalendar.options.planning_begin || "08:00:00",
      maxTime: ITSMReservationCalendar.options.planning_end || "20:00:00",
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
        url: ITSMReservationCalendar.options.ajax_url,
        type: "POST",
        extraParams: function () {
          return {
            action: "get_events",
            reservationitems_id: ITSMReservationCalendar.options.reservationitems_id,
          };
        },
      },
      eventRender: function (info) {
        const event = info.event;
        const props = event.extendedProps || {};

        info.el.setAttribute("data-testid", "reservation-calendar-event");
        info.el.setAttribute("data-reservation-id", event.id);

        if (props.comment) {
          info.el.setAttribute("title", props.comment);
        }

        if (info.view.type.indexOf("timeGrid") === 0) {
          $(info.el).find(".fc-title").prepend('<i class="fas fa-lock me-1" aria-hidden="true"></i>');
        }
      },
      select: function (info) {
        if (!ITSMReservationCalendar.options.can_reserve) {
          ITSMReservationCalendar.calendar.unselect();
          return;
        }

        ITSMReservationCalendar.openForm({
          reservationitems_id: ITSMReservationCalendar.options.reservationitems_id,
          begin: ITSMReservationCalendar.formatDate(info.start),
          end: ITSMReservationCalendar.formatDate(info.end),
        });
        ITSMReservationCalendar.calendar.unselect();
      },
      eventClick: function (info) {
        const props = info.event.extendedProps || {};
        info.jsEvent.preventDefault();

        if (props.can_edit) {
          ITSMReservationCalendar.openForm({
            id: info.event.id,
          });
        }
      },
      eventDrop: function (info) {
        ITSMReservationCalendar.updateEventTimes(info);
      },
      eventResize: function (info) {
        ITSMReservationCalendar.updateEventTimes(info);
      },
    });

    const loadedLocales = typeof FullCalendarLocales !== "undefined" ? Object.keys(FullCalendarLocales) : [];
    if (loadedLocales.length === 1) {
      ITSMReservationCalendar.calendar.setOption("locale", loadedLocales[0]);
    }

    ITSMReservationCalendar.calendar.render();
  }

  openForm(params) {
    const dialog = $("<div class='reservation-calendar-dialog'></div>");
    const data = Object.assign({ action: "get_form" }, params || {});

    dialog.dialog({
      modal: true,
      width: Math.min($(window).width() - 40, 760),
      maxHeight: $(window).height() - 80,
      title: __("Reservation"),
      open: function () {
        dialog.load(ITSMReservationCalendar.options.ajax_url, data, function () {
          ITSMReservationCalendar.bindForm(dialog);
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
        url: ITSMReservationCalendar.options.ajax_url,
        type: "POST",
        dataType: "json",
        data: $.param(data),
        success: function (response) {
          if (response && response.success) {
            dialog.dialog("close");
            ITSMReservationCalendar.refresh();
            window.displayAjaxMessageAfterRedirect();
            return;
          }

          if (response && response.html) {
            dialog.find(".reservation-calendar-form-error").remove();
            dialog.prepend("<div class='reservation-calendar-form-error'>" + response.html + "</div>");
          }
        },
      });
    });
  }

  refresh() {
    if (ITSMReservationCalendar.calendar) {
      ITSMReservationCalendar.calendar.refetchEvents();
    }
  }

  updateEventTimes(info) {
    const event = info.event;

    $.ajax({
      url: ITSMReservationCalendar.options.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "update_times",
        id: event.id,
        begin: ITSMReservationCalendar.formatDate(event.start),
        end: ITSMReservationCalendar.formatDate(event.end),
      },
      success: function (response) {
        if (response && response.success) {
          ITSMReservationCalendar.refresh();
          return;
        }

        info.revert();
        if (response && response.html) {
          $("<div></div>").html(response.html).dialog({
            modal: true,
            width: "auto",
            title: __("Reservation"),
          });
        }
      },
      error: function () {
        info.revert();
      },
    });
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

const ITSMReservationCalendar = new ReservationCalendar();
window.ITSMReservationCalendar = ITSMReservationCalendar;
