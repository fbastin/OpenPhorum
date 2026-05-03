<!-- BEGIN TEMPLATE mods/user_image_gallery/templates/emerald/report_image.tpl -->

<form action="{URL->ACTION}" method="post">
    {POST_VARS}
    <input type="hidden" name="action" value="report_image" />
    <input type="hidden" name="image_number" value="{image_number}" />
    <input type="hidden" name="image_owner" value="{IMAGE->user_id}" />
    <input type="hidden" name="current_user" value="{USER->user_id}" />

    <input name="report_image" type="submit" value=" {LANG->mod_user_image_gallery->Report_this_image} " />

</form>
<!-- END TEMPLATE mods/user_image_gallery/templates/emerald/report_image.tpl -->
