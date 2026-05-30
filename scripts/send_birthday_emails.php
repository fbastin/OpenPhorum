<?php
/**
 * Script d'envoi automatique des courriels d'anniversaire pour Tireur.org
 * Doit être exécuté une fois par jour via Cron.
 */

// Définition de l'environnement Phorum
define('PHORUM', 1);
define('PHORUM_ADMIN', 1); // Pour avoir accès à certaines fonctions si besoin
chdir(__DIR__ . '/..'); // Se placer à la racine du forum
require_once('./common.php');
require_once('./include/email_functions.php');
require_once('./include/api/mail.php');

// Paramètres de l'email (utilisent les réglages système de Phorum désormais configurés pour Gmail)
$from_email = $PHORUM['system_email_from_address'];
$from_name  = $PHORUM['system_email_from_name'];
$subject    = '🎂 Joyeux anniversaire de la part de Tireur.org !';

// Date du jour
$today_md = date('m-d');
$current_year = date('Y');

// 1. Protection contre les envois multiples le même jour
$last_run = isset($PHORUM['mod_birthday_email_last_run']) ? $PHORUM['mod_birthday_email_last_run'] : '';
if ($last_run == date('Y-m-d')) {
    echo "Script déjà exécuté aujourd'hui (" . date('Y-m-d') . "). Arrêt.\n";
    exit;
}

// 2. Recherche des membres fêtant leur anniversaire aujourd'hui
// On utilise le champ personnalisé type 22 (user_birthday)
$prefix = $PHORUM['DBCONFIG']['table_prefix'];
$sql = "SELECT u.user_id, u.username, u.email, b.data as birthday 
        FROM {$prefix}_users u 
        JOIN {$prefix}_user_custom_fields b ON u.user_id = b.user_id AND b.type = 22
        WHERE b.data LIKE '%-$today_md' 
        AND u.active = 1";

$res = phorum_db_interact(DB_RETURN_RES, $sql);

if (!$res) {
    echo "Aucun anniversaire aujourd'hui ($today_md).\n";
} else {
    $count = 0;
    while ($row = phorum_db_fetch_row($res, DB_RETURN_ASSOC)) {
        $username = $row['username'];
        $email    = $row['email'];
        $bday     = $row['birthday'];
        
        // Calcul de l'âge
        $age = "";
        if (preg_match('/^(\d{4})/', $bday, $m)) {
            $year = (int)$m[1];
            $age = $current_year - $year;
        }

        echo "Envoi à $username ($email)... ";

        // Préparation du message
        $message = "Bonjour $username,\n\n";
        $message .= "Toute l'équipe de Tireur.org est ravie de vous souhaiter un très joyeux anniversaire";
        if ($age) {
            $message .= " pour vos $age ans";
        }
        $message .= " !\n\n";
        $message .= "Nous vous remercions de votre fidélité et de votre participation à notre communauté francophone de tir sportif.\n\n";
        $message .= "À très bientôt sur le forum,\n\n";
        $message .= "L'équipe Tireur.org\nhttps://www.tireur.org";

        // Envoi via l'API Phorum
        $mail_data = array(
            'from_address' => phorum_api_mail_encode_header($from_name) . " <$from_email>",
            'mailsubject'  => $subject,
            'mailmessage'  => $message
        );

        phorum_email_user(array($email), $mail_data);
        echo "OK.\n";
        $count++;
    }
    echo "$count email(s) envoyé(s).\n";
}

// 3. Mise à jour de la date de dernière exécution
phorum_db_update_settings(array('mod_birthday_email_last_run' => date('Y-m-d')));

?>
