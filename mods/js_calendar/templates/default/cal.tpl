<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.css">
<script src="https://cdn.jsdelivr.net/npm/simple-jscalendar@1.4.5/source/jsCalendar.min.js"></script>
<link rel="stylesheet" href="/forum/mods/js_calendar/css/calendar.css?v=20260525_FIX4">

<script type="text/javascript">
var phorumCalendarConfig = {
    ajaxUrl: "{CALENDAR->ajax_url}",
    userId: {CALENDAR->user_id},
    isAdmin: {CALENDAR->is_admin}
};
</script>

<div class="PhorumStdBlockHeader PhorumHeaderText">Calendrier des &eacute;v&eacute;nements</div>
<div class="PhorumStdBlock calendar-page-main">
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
