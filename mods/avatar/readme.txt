Avatar Module Setup - Version 3.x
---------------------------------

This version of the Avatar module is updated for and tested against
Phorum 5.1.4-alpha. This Phorum version uses a new template construction
for inserting the pull down list with available avatars in the 
message editor form ({HOOK tpl_editor_after_subject}).

To use this module, the following must be done:

- Upload the module file to a directory named "mods/avatar" inside your
  Phorum directory and enable and configure the avatar module from the
  admin interface.

- File Uploads must be enabled in Phorum's 'General Settings'. You need to
  make sure to allow at least one image filetype, such as .gif, .jpg, or
  .png. You can allow all of them.

- In Phorum's "Custom Profiles" option, create a field called "mod_avatar".
  Field Name   : mod_avatar
  Field Length : 1000
  Disable HTML : no (so not checked)

  (Field length determines the maximum number of avatars a user can store.
  There's no good indication of how many avatars you can store in a 
  certain length, because it depends on the lengths of the used labels,
  but 1000 should be plenty for anybody).

- In the mods/avatar directory, there are two files that have to be copied.

  * mod_avatar.php must be copied to /include/controlcenter inside your
    Phorum directory

  * cc_mod_avatar.tpl must be copied into the folder for every template
    you are using

- Templates must be changed to show avatars. Suggested template
  changes are included below, feel free to alter them to suit your needs.


cc_menu.tpl (wherever you want the link to the avatar editing page to appear
in the control center)
-------------------
{IF mod_avatar->enabled}<li><a {IF PROFILE->PANEL "mod_avatar"}class="phorum-current-page" {/IF} href="{mod_avatar->url}">{LANG->mod_avatar->EditAvatars}</a></li>{/IF}

read.tpl (I put this just above the subject line, the avatar will sit on the
right side of the post in that case, but you can put it anywhere)
-------------------
{IF MESSAGES->mod_avatar}
<img src="{MESSAGES->mod_avatar}" alt="{LANG->mod_avatar->Avatar}" align="right" />
{/IF}

profile.tpl (if you want the users default avatar to appear in their profile,
put this same code somewhere)
-----------------------------
{IF PROFILE->mod_avatar_url}
<img src="{PROFILE->mod_avatar_url}" alt="{LANG->mod_avatar->Avatar}" align="right" />
{/IF}

read_threads.tpl (I put this just above the subject line, the avatar will sit
on the right side of the post in that case, but you can put it anywhere)
---------------------------
{IF MESSAGE->mod_avatar}
<img src="{MESSAGE->mod_avatar}" alt="{LANG->mod_avatar->Avatar}" align="right" />
{/IF}
