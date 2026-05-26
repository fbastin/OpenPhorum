var phorumCalendarEdit, phorumCalendarDelete;

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jsCalendar === 'undefined') return;
        if (typeof phorumCalendarConfig === 'undefined') return;

        var config = phorumCalendarConfig;
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
            return config.ajaxUrl + ",action=" + action + ",_t=" + Date.now();
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
            if (!eventsContainer) return;
            eventsContainer.innerHTML = "<div class='loader'>Chargement...</div>";
            fetch(getUrl("list") + ",date=" + date)
                .then(r => r.json())
                .then(data => {
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        eventsContainer.innerHTML = "<p class='no-events'>Aucun événement prévu ce jour.</p>";
                    } else {
                        var html = '';
                        data.forEach((ev, idx) => {
                            var canEdit = config.userId > 0 && (config.isAdmin || config.userId == ev.user_id);
                            html += '<div class="event-item' + (canEdit ? ' editable' : '') + '" data-idx="' + idx + '">';
                            if (canEdit) {
                                html += '<span class="btn-delete-event" title="Supprimer" data-id="' + ev.event_id + '">&times;</span>';
                            }
                            html += '<div class="event-title">' + escapeHtml(ev.title) + '</div>';
                            html += '<div class="event-author">Par ' + escapeHtml(ev.username) + '</div>';
                            if (ev.description) {
                                html += '<div class="event-desc">' + escapeHtml(ev.description) + '</div>';
                            }
                            html += '</div>';
                        });
                        eventsContainer.innerHTML = html;
                        
                        document.querySelectorAll('.event-item').forEach(el => {
                            el.onclick = function() {
                                var idx = this.getAttribute('data-idx');
                                phorumCalendarEdit(data[idx]);
                            };
                        });
                        document.querySelectorAll('.btn-delete-event').forEach(el => {
                            el.onclick = function(e) {
                                e.stopPropagation();
                                var id = this.getAttribute('data-id');
                                phorumCalendarDelete(id, date);
                            };
                        });
                    }
                });
        };

        phorumCalendarEdit = function(ev) {
            if (!eventTitleInput) return;
            eventIdInput.value = ev.event_id;
            eventTitleInput.value = ev.title;
            eventDescInput.value = ev.description || '';
            eventDateInput.value = ev.event_date || ev.date;
            formActionTitle.innerText = "Modifier l'événement";
            if (btnCancel) btnCancel.style.display = 'inline-block';
            document.getElementById('event-editor-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
            eventTitleInput.focus();
        };

        phorumCalendarDelete = function(eventId, date) {
            if (confirm("Voulez-vous vraiment supprimer cet événement ?")) {
                fetch(getUrl("delete") + ",event_id=" + eventId)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            fetchEvents(date);
                            highlightEventDates();
                            if (eventIdInput && eventIdInput.value == eventId) resetForm();
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
            if (btnCancel) btnCancel.style.display = 'none';
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
