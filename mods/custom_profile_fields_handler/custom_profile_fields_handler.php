<?php
if (!defined("PHORUM")) return;

function phorum_mod_custom_profile_fields_handler_save($userdata)
{
    // On ne s'occupe que du panneau "user" (Mon Profil)
    if (isset($userdata['panel']) && $userdata['panel'] != "user") {
        return $userdata;
    }

    // 1. Validation de la date de naissance
    if (isset($_POST['user_birthday']) && trim($_POST['user_birthday']) !== "") {
        $birthday = trim($_POST['user_birthday']);
        
        // Vérification du format
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $birthday, $matches)) {
            $userdata['error'] = "Le format de la date de naissance doit être AAAA-MM-JJ (ex: 1980-05-15).";
            return $userdata;
        }
        
        $y = (int)$matches[1];
        $m = (int)$matches[2];
        $d = (int)$matches[3];
        
        // Vérification de la validité de la date (ex: pas de 31 février)
        if (!checkdate($m, $d, $y)) {
            $userdata['error'] = "La date de naissance saisie est invalide.";
            return $userdata;
        }
        
        // Vérification que la date n'est pas dans le futur
        if (mktime(0, 0, 0, $m, $d, $y) > time()) {
            $userdata['error'] = "La date de naissance ne peut pas être dans le futur.";
            return $userdata;
        }
        
        $userdata['user_birthday'] = $birthday;
    } else {
        $userdata['user_birthday'] = "";
    }

    // 2. Gestion de la case à cocher "Confidentialité"
    // Si la case n'est pas cochée, PHP ne l'envoie pas dans $_POST.
    if (!isset($_POST['user_birthday_privacy'])) {
        $userdata['user_birthday_privacy'] = 0;
    } else {
        $userdata['user_birthday_privacy'] = 1;
    }

    return $userdata;
}
?>
