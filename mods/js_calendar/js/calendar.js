document.addEventListener('DOMContentLoaded', function() {
    if (typeof jsCalendar === 'undefined') {
        console.error('jsCalendar library not loaded');
        return;
    }
    
    var calendar = jsCalendar.new("#my-calendar");
    var eventsContainer = document.getElementById('events-container');
    var selectedDateStr = document.getElementById('selected-date-str');
    var eventDateInput = document.getElementById('event-date');
    
    // Config from global object set in template
    var ajaxUrlBase = PhorumCalendarConfig.ajax_url;
    var currentUserId = PhorumCalendarConfig.user_id;
    var isAdmin = PhorumCalendarConfig.is_admin;
    
    var getUrl = function(action) {
        return ajaxUrlBase + (ajaxUrlBase.indexOf('?') === -1 ? '?' : '&') + "action=" + action;
    };

    var today = new Date();
    updateDateSelection(today);
    highlightEventDates();

    calendar.onDateClick(function(event, date) {
        updateDateSelection(date);
    });

    if (eventDateInput) {
        eventDateInput.addEventListener('change', function() {
            var dateParts = this.value.split('-');
            if (dateParts.length === 3) {
                var newDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                calendar.set(newDate);
                updateDateSelection(newDate);
            }
        });
    }

    function updateDateSelection(date) {
        var dateStr = jsCalendar.tools.dateToString(date, "YYYY-MM-DD", "en");
        selectedDateStr.innerText = jsCalendar.tools.dateToString(date, "DD/MM/YYYY", "fr");
        if (eventDateInput) eventDateInput.value = dateStr;
        fetchEvents(dateStr);
    }

    function highlightEventDates() {
        fetch(getUrl("list_all_dates"))
            .then(r => r.json())
            .then(dates => {
                calendar.unselect();
                if (Array.isArray(dates)) calendar.select(dates);
            })
            .catch(e => console.error("Highlight error", e));
    }

    function fetchEvents(date) {
        eventsContainer.innerHTML = "<em>Chargement...</em>";
        fetch(getUrl("list") + "&date=" + date)
            .then(r => r.json())
            .then(data => {
                if (!data || !Array.isArray(data) || data.length === 0) {
                    eventsContainer.innerHTML = "Aucun &eacute;v&eacute;nement pr&eacute;vu.";
                } else {
                    var html = '';
                    data.forEach(ev => {
                        html += '<div class="event-item">';
                        if (isAdmin || currentUserId == ev.user_id) {
                            html += '<span class="btn-delete-event" title="Supprimer" onclick="deleteEvent(' + ev.event_id + ', \'' + date + '\')">&times;</span>';
                        }
                        html += '<strong>' + escapeHtml(ev.title) + '</strong> <small>(par ' + escapeHtml(ev.username) + ')</small><br>';
                        html += '<div style="color: #666; font-size: 0.9em; white-space: pre-wrap;">' + (ev.description ? escapeHtml(ev.description) : "") + '</div>';
                        html += '</div>';
                    });
                    eventsContainer.innerHTML = html;
                }
            })
            .catch(e => {
                eventsContainer.innerHTML = '<span style="color:red">Erreur de chargement.</span>';
                console.error(e);
            });
    }

    window.deleteEvent = function(eventId, date) {
        if (confirm("Voulez-vous vraiment supprimer cet événement ?")) {
            fetch(getUrl("delete") + "&event_id=" + eventId)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fetchEvents(date);
                        highlightEventDates();
                    } else {
                        alert("Erreur: " + data.error);
                    }
                });
        }
    }

    var btnSave = document.getElementById('btn-save-event');
    if (btnSave) {
        btnSave.addEventListener('click', function() {
            var formData = new FormData(document.getElementById('new-event-form'));
            btnSave.disabled = true;
            fetch(getUrl("save"), { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert("Évènement enregistré !");
                        document.getElementById('event-title').value = '';
                        document.getElementById('event-desc').value = '';
                        var targetDate = document.getElementById('event-date').value;
                        fetchEvents(targetDate);
                        highlightEventDates();
                    } else {
                        alert("Erreur: " + data.error);
                    }
                })
                .finally(() => { btnSave.disabled = false; });
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
