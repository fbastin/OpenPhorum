  <?php

  if (!defined("PHORUM_ADMIN")) return;

  // If data is posted, then store the posted settings in the database.
  if (count($_POST))
  {
        $PHORUM['social_share']['share_twitter'] = empty($_POST['share_twitter']) ? 0 : 1;
        $PHORUM['social_share']['share_facebook'] = empty($_POST['share_facebook']) ? 0 : 1;
        $PHORUM['social_share']['share_google-plus'] = empty($_POST['share_google-plus']) ? 0 : 1;
        $PHORUM['social_share']['link_new_window'] = empty($_POST['link_new_window']) ? 0 : 1;

      // The data was okay. Store the settings.
          phorum_db_update_settings(array("social_share" => $PHORUM["social_share"]));
          phorum_admin_okmsg('The settings were saved successfully');
  }

  // This block is standard for every settings page. The "mod" field
  // must be set to the name of the module for which the settings
  // page is written.
  include_once "./include/admin/PhorumInputForm.php";
  $frm = new PhorumInputForm ("", "post", "Submit this form");
  $frm->hidden("module", "modsettings");
  $frm->hidden("mod", "social_share");

  // Add a header row to the form.
  $frm->addbreak("Social Media Sharing module settings");

  // Add a text field to the form.
  $frm->addrow(
      "Share to Twitter?",
      $frm->checkbox("share_twitter", "1", "Yes", $PHORUM['social_share']['share_twitter'])
  );
  $frm->addrow(
      "Share to Facebook?",
      $frm->checkbox("share_facebook", "1", "Yes", $PHORUM['social_share']['share_facebook'])
  );
  $frm->addrow(
      "Share to Google+?",
      $frm->checkbox("share_google-plus", "1", "Yes", $PHORUM['social_share']['share_google-plus'])
  );
  $frm->addrow(
      "Open Links in New Window?",
      $frm->checkbox("link_new_window", "1", "Yes", $PHORUM['social_share']['link_new_window'])
  );


  // Display the form.
  $frm->show();

  ?>
