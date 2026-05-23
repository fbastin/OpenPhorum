<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.css">
<script src="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.js"></script>
<link rel="stylesheet" href="/forum/mods/js_calendar/css/calendar.css">

<div class="PhorumStdBlockHeader PhorumHeaderText">Calendrier des &eacute;v&eacute;nements</div>
<div class="PhorumStdBlock">
    <div style="display: flex; flex-wrap: wrap; gap: 2rem; padding: 1rem;">
        <div id="my-calendar" data-language="fr"></div>
        <div id="event-manager" style="flex: 1; min-width: 300px;">
            <div id="event-list">
                <h3>&Eacute;v&eacute;nements le <span id="selected-date-str">-</span></h3>
                <div id="events-container">Cliquer sur une date pour voir les &eacute;v&eacute;nements.</div>
            </div>

            {IF CALENDAR->is_logged_in}
            <div id="add-event-form" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ccc;">
                <h3>Ajouter un &eacute;v&eacute;nement</h3>
                <form id="new-event-form">
                    <div style="margin-bottom: 10px;">
                        <label>Date :</label><br>
                        <input type="date" id="event-date" name="date" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Titre :</label><br>
                        <input type="text" id="event-title" name="title" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Description :</label><br>
                        <textarea id="event-desc" name="description" style="width: 100%; height: 80px;"></textarea>
                    </div>
                    <button type="button" id="btn-save-event" class="PhorumSubmit">Enregistrer</button>
                </form>
            </div>
            {ELSE}
            <p style="margin-top: 2rem; color: #666;">
                <em>Vous devez être <a href="{URL->LOGINOUT}">identifié</a> pour ajouter des événements.</em>
            </p>
            {/IF}
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    // Config injected via template
    var ajaxUrl = "{CALENDAR->ajax_url}";
    var userId = {CALENDAR->user_id};
    var isAdmin = {CALENDAR->is_admin};
    
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jsCalendar === 'undefined') return;
        
        var calendar = jsCalendar.new("#my-calendar");
        var eventsContainer = document.getElementById('events-container');
        var selectedDateStr = document.getElementById('selected-date-str');
        var eventDateInput = document.getElementById('event-date');
        
        var getUrl = function(action) {
            return ajaxUrl + ",action=" + action + ",_t=" + Date.now();
        };

        var updateDateSelection = function(date) {
            var dateStr = jsCalendar.tools.dateToString(date, "YYYY-MM-DD", "en");
            selectedDateStr.innerText = jsCalendar.tools.dateToString(date, "DD/MM/YYYY", "fr");
            if (eventDateInput) eventDateInput.value = dateStr;
            fetchEvents(dateStr);
        };

        var toDateObj = function(str) {
            var p = str.split('-');
            return new Date(p[0], p[1] - 1, p[2]);
        };

        var highlightEventDates = function() {
            fetch(getUrl("list_all_dates"))
                .then(r => r.json())
                .then(dates => {
                    calendar.clearselect();
                    if (Array.isArray(dates)) calendar.select(dates.map(toDateObj));
                });
        };

        var fetchEvents = function(date) {
            eventsContainer.innerHTML = "<em>Chargement...</em>";
            fetch(getUrl("list") + ",date=" + date)
                .then(r => r.json())
                .then(data => {
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        eventsContainer.innerHTML = "Aucun &eacute;v&eacute;nement pr&eacute;vu.";
                    } else {
                        var html = '';
                        data.forEach(ev => {
                            html += '<div class="event-item">';
                            if (userId > 0 && (isAdmin || userId == ev.user_id)) {
                                html += '<span class="btn-delete-event" title="Supprimer" onclick="window.phorumCalendarDelete(' + ev.event_id + ', \'' + date + '\')">&times;</span>';
                            }
                            html += '<strong>' + escapeHtml(ev.title) + '</strong> <small>(par ' + escapeHtml(ev.username) + ')</small><br>';
                            html += '<div style="color: #666; font-size: 0.9em; white-space: pre-wrap;">' + (ev.description ? escapeHtml(ev.description) : "") + '</div>';
                            html += '</div>';
                        });
                        eventsContainer.innerHTML = html;
                    }
                });
        };

        window.phorumCalendarDelete = function(eventId, date) {
            if (confirm("Voulez-vous vraiment supprimer cet événement ?")) {
                fetch(getUrl("delete") + ",event_id=" + eventId)
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
        };

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

        var today = new Date();
        updateDateSelection(today);
        highlightEventDates();
        
        calendar.onDateClick(function(event, date) {
            updateDateSelection(date);
        });

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
})();
</script>
