<?php

if (!defined('PHORUM')) return;

/**
 * Addon hook to display the js_calendar page.
 */
function phorum_mod_js_calendar_addon() {
    $PHORUM = $GLOBALS['PHORUM'];

    // Determine action from URL args
    $action = 'display';
    if (isset($PHORUM['args']['action'])) {
        $action = $PHORUM['args']['action'];
    } elseif (isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
    }

    // Handle AJAX actions
    if ($action !== 'display') {
        // Clean output buffer to ensure pure JSON
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        
        $data = array();
        try {
            if ($action === 'list') {
                $data = phorum_mod_js_calendar_list_events();
            } elseif ($action === 'list_all_dates') {
                $data = phorum_mod_js_calendar_list_all_dates();
            } elseif ($action === 'save') {
                $data = phorum_mod_js_calendar_save_event();
            } elseif ($action === 'delete') {
                $data = phorum_mod_js_calendar_delete_event();
            }
        } catch (Exception $e) {
            $data = array('error' => $e->getMessage());
        }
        
        echo json_encode($data);
        exit;
    }

    // Standard display logic
    phorum_build_common_urls();

    $is_logged_in = !empty($PHORUM['user']['user_id']);

    $GLOBALS['PHORUM']['DATA']['CALENDAR'] = array(
        'is_logged_in' => $is_logged_in,
        'user_id' => $is_logged_in ? (int)$PHORUM['user']['user_id'] : 0,
        'is_admin' => !empty($PHORUM['user']['admin']) ? 1 : 0,
        'ajax_url' => phorum_get_url(PHORUM_ADDON_URL, 'module=js_calendar')
    );

    phorum_output('js_calendar::cal');
}

/**
 * Register CSS files
 */
function phorum_mod_js_calendar_css_register($data) {
    if ($GLOBALS['PHORUM']['page'] !== 'addon' || !isset($_REQUEST['module']) || $_REQUEST['module'] !== 'js_calendar') {
        return $data;
    }
    
    $data['register'][] = array(
        'module' => 'js_calendar',
        'where'  => 'after',
        'source' => 'file(mods/js_calendar/css/calendar.css)'
    );
    
    return $data;
}

/**
 * Register JavaScript files
 */
function phorum_mod_js_calendar_javascript_register($data) {
    if ($GLOBALS['PHORUM']['page'] !== 'addon' || !isset($_REQUEST['module']) || $_REQUEST['module'] !== 'js_calendar') {
        return $data;
    }

    $data[] = array(
        'module' => 'js_calendar',
        'source' => 'file(mods/js_calendar/js/calendar.js)'
    );
    
    return $data;
}

function phorum_mod_js_calendar_list_events() {
    $PHORUM = $GLOBALS['PHORUM'];
    $date = isset($_REQUEST['date']) ? $_REQUEST['date'] : (isset($PHORUM['args']['date']) ? $PHORUM['args']['date'] : '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return array('error' => 'Date invalide');

    $prefix = $PHORUM['DBCONFIG']['table_prefix'];
    $quoted_date = phorum_db_interact(DB_RETURN_QUOTED, $date);

    $sql = "SELECT e.*, u.username FROM phorum_calendar_events e JOIN {$prefix}_users u ON e.user_id = u.user_id WHERE e.event_date = '$quoted_date' ORDER BY e.created_at ASC";
    $res = phorum_db_interact(DB_RETURN_RES, $sql);

    $results = array();
    if ($res) {
        while ($row = phorum_db_fetch_row($res, DB_RETURN_ASSOC)) {
            $results[] = $row;
        }
    }
    return $results;
}

function phorum_mod_js_calendar_list_all_dates() {
    $res = phorum_db_interact(DB_RETURN_RES, "SELECT DISTINCT event_date FROM phorum_calendar_events");
    $results = array();
    if ($res) {
        while ($row = phorum_db_fetch_row($res, DB_RETURN_ASSOC)) {
            $results[] = $row['event_date'];
        }
    }
    return $results;
}

function phorum_mod_js_calendar_save_event() {
    $PHORUM = $GLOBALS['PHORUM'];
    if (empty($PHORUM['user']['user_id'])) return array('success' => false, 'error' => 'Non connecté');

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    if (empty($title) || empty($date)) return array('success' => false, 'error' => 'Champs requis manquants');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return array('success' => false, 'error' => 'Date invalide');

    $quoted_title = phorum_db_interact(DB_RETURN_QUOTED, $title);
    $quoted_desc = phorum_db_interact(DB_RETURN_QUOTED, $desc);
    $quoted_date = phorum_db_interact(DB_RETURN_QUOTED, $date);
    $user_id = (int)$PHORUM['user']['user_id'];

    if ($event_id > 0) {
        $res = phorum_db_interact(DB_RETURN_RES, "SELECT user_id FROM phorum_calendar_events WHERE event_id = $event_id");
        $row = $res ? phorum_db_fetch_row($res, DB_RETURN_ASSOC) : null;
        if (!$row) return array('success' => false, 'error' => 'Événement introuvable');
        if ($row['user_id'] != $user_id && empty($PHORUM['user']['admin'])) {
            return array('success' => false, 'error' => 'Accès interdit');
        }
        $sql = "UPDATE phorum_calendar_events SET title = '$quoted_title', description = '$quoted_desc', event_date = '$quoted_date' WHERE event_id = $event_id";
    } else {
        $sql = "INSERT INTO phorum_calendar_events (user_id, title, description, event_date) VALUES ($user_id, '$quoted_title', '$quoted_desc', '$quoted_date')";
    }

    phorum_db_interact(DB_RETURN_RES, $sql);
    return array('success' => true);
}

function phorum_mod_js_calendar_delete_event() {
    $PHORUM = $GLOBALS['PHORUM'];
    if (empty($PHORUM['user']['user_id'])) return array('success' => false, 'error' => 'Non connecté');

    $event_id = isset($_REQUEST['event_id']) ? (int)$_REQUEST['event_id'] : (isset($PHORUM['args']['event_id']) ? (int)$PHORUM['args']['event_id'] : 0);
    
    $res = phorum_db_interact(DB_RETURN_RES, "SELECT user_id FROM phorum_calendar_events WHERE event_id = $event_id");
    if (!$res) return array('success' => false, 'error' => 'Event introuvable');
    $row = phorum_db_fetch_row($res, DB_RETURN_ASSOC);

    if (!$row || ($row['user_id'] != $PHORUM['user']['user_id'] && empty($PHORUM['user']['admin']))) {
        return array('success' => false, 'error' => 'Accès interdit');
    }

    phorum_db_interact(DB_RETURN_RES, "DELETE FROM phorum_calendar_events WHERE event_id = $event_id");
    return array('success' => true);
}
