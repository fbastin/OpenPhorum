<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.css">
<script src="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.js"></script>
<link rel="stylesheet" href="/forum/mods/js_calendar/css/calendar.css?v=20260525_FIX">

<div class="PhorumStdBlockHeader PhorumHeaderText">Calendrier des &eacute;v&eacute;nements</div>
<div class="PhorumStdBlock calendar-page-main">
    <!-- Version Marker: V5_STABLE -->
    <div class="calendar-layout-container">
        <!-- Calendar View -->
        <div class="calendar-view-section">
            <div id="my-calendar" data-language="fr"></div>
        </div>
        
        <!-- Details & Editor Panel -->
        <div id="event-manager" class="calendar-details-section">
            <div id="event-list" class="event-list-panel">
                <h3 class="panel-title">&Eacute;v&eacute;nements le <span id="selected-date-str">-</span></h3>
                <div id="events-container" class="events-scroll">Cliquer sur une date pour voir les &eacute;v&eacute;nements.</div>
            </div>

            {IF CALENDAR->is_logged_in}
            <div id="event-editor-section" class="event-form-panel">
                <h3 id="form-action-title">Ajouter un &eacute;v&eacute;nement</h3>
                <form id="event-form">
                    <input type="hidden" id="event-id" name="event_id" value="">
                    <div class="form-group">
                        <label>Date :</label>
                        <input type="date" id="event-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label>Titre :</label>
                        <input type="text" id="event-title" name="title" required placeholder="Titre de l'événement">
                    </div>
                    <div class="form-group">
                        <label>Description :</label>
                        <textarea id="event-desc" name="description" placeholder="Détails, horaires, liens..."></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" id="btn-save-event" class="PhorumSubmit">Enregistrer</button>
                        <button type="button" id="btn-cancel-edit" class="btn-secondary" style="display:none">Annuler</button>
                    </div>
                </form>
            </div>
            {ELSE}
            <div class="event-form-panel">
                <p style="color: #666; font-style: italic; margin: 0;">
                    Connectez-vous pour proposer un &eacute;v&eacute;nement.
                </p>
            </div>
            {/IF}
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var ajaxUrl = "{CALENDAR->ajax_url}";
    var userId = {CALENDAR->user_id};
    var isAdmin = {CALENDAR->is_admin};
    
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jsCalendar === 'undefined') return;
        
        var calendar = jsCalendar.new("#my-calendar");
        
        var eventsContainer = document.getElementById('events-container');
        var selectedDateStr = document.getElementById('selected-date-str');
        var eventDateInput = document.getElementById('event-date');
        var eventTitleInput = document.getElementById('event-title');
        var eventDescInput = document.getElementById('event-desc');
        var eventIdInput = document.getElementById('event-id');
        var formActionTitle = document.getElementById('form-action-title');
        var btnCancel = document.getElementById('btn-cancel-edit');
        
        var getUrl = function(action) {
            return ajaxUrl + ",action=" + action + ",_t=" + Date.now();
        };

        var toDateObj = function(str) {
            if (!str) return new Date();
            var p = str.split('-');
            return new Date(p[0], p[1] - 1, p[2]);
        };

        var updateDateSelection = function(date, skipFormUpdate) {
            var dateStr = jsCalendar.tools.dateToString(date, "YYYY-MM-DD", "en");
            selectedDateStr.innerText = jsCalendar.tools.dateToString(date, "DD/MM/YYYY", "fr");
            if (eventDateInput && !skipFormUpdate) {
                eventDateInput.value = dateStr;
            }
            fetchEvents(dateStr);
        };

        var highlightEventDates = function() {
            fetch(getUrl("list_all_dates"))
                .then(r => r.json())
                .then(dates => {
                    calendar.clearselect();
                    if (Array.isArray(dates)) {
                        calendar.select(dates.map(toDateObj));
                    }
                });
        };

        var fetchEvents = function(date) {
            eventsContainer.innerHTML = "<div class='loader'>Chargement...</div>";
            fetch(getUrl("list") + ",date=" + date)
                .then(r => r.json())
                .then(data => {
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        eventsContainer.innerHTML = "<p class='no-events'>Aucun &eacute;v&eacute;nement pr&eacute;vu ce jour.</p>";
                    } else {
                        var html = '';
                        data.forEach(ev => {
                            var canEdit = userId > 0 && (isAdmin || userId == ev.user_id);
                            html += '<div class="event-item' + (canEdit ? ' editable' : '') + '" onclick=\'window.phorumCalendarEdit(' + JSON.stringify(ev).replace(/'/g, "&#39;") + ')\'>';
                            if (canEdit) {
                                html += '<span class="btn-delete-event" title="Supprimer" onclick="window.phorumCalendarDelete(event, ' + ev.event_id + ', \'' + date + '\')">&times;</span>';
                            }
                            html += '<div class="event-title">' + escapeHtml(ev.title) + '</div>';
                            html += '<div class="event-author">Par ' + escapeHtml(ev.username) + '</div>';
                            if (ev.description) {
                                html += '<div class="event-desc">' + escapeHtml(ev.description) + '</div>';
                            }
                            html += '</div>';
                        });
                        eventsContainer.innerHTML = html;
                    }
                });
        };

        window.phorumCalendarEdit = function(ev) {
            if (!eventTitleInput) return;
            eventIdInput.value = ev.event_id;
            eventTitleInput.value = ev.title;
            eventDescInput.value = ev.description || '';
            eventDateInput.value = ev.event_date || ev.date;
            formActionTitle.innerText = "Modifier l'événement";
            btnCancel.style.display = 'inline-block';
            document.getElementById('event-editor-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
            eventTitleInput.focus();
        };

        window.phorumCalendarDelete = function(e, eventId, date) {
            e.stopPropagation();
            if (confirm("Voulez-vous vraiment supprimer cet événement ?")) {
                fetch(getUrl("delete") + ",event_id=" + eventId)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            fetchEvents(date);
                            highlightEventDates();
                            if (eventIdInput.value == eventId) resetForm();
                        } else {
                            alert("Erreur: " + data.error);
                        }
                    });
            }
        };

        function resetForm() {
            if (!eventTitleInput) return;
            eventIdInput.value = '';
            eventTitleInput.value = '';
            eventDescInput.value = '';
            formActionTitle.innerText = "Ajouter un événement";
            btnCancel.style.display = 'none';
        }

        if (btnCancel) btnCancel.onclick = resetForm;

        var btnSave = document.getElementById('btn-save-event');
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                var title = eventTitleInput.value.trim();
                if (!title) { alert("Saisissez un titre."); return; }
                var formData = new FormData(document.getElementById('event-form'));
                btnSave.disabled = true;
                fetch(getUrl("save"), { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            resetForm();
                            var targetDate = eventDateInput.value;
                            fetchEvents(targetDate);
                            highlightEventDates();
                        } else {
                            alert("Erreur: " + data.error);
                        }
                    })
                    .finally(() => { btnSave.disabled = false; });
            });
        }

        var today = new Date();
        updateDateSelection(today);
        highlightEventDates();
        
        calendar.onDateClick(function(event, date) {
            updateDateSelection(date);
        });

        function escapeHtml(text) {
            if (!text) return "";
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
})();
</script>
