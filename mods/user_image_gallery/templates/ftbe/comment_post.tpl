<!-- BEGIN TEMPLATE mods/user_image_gallery/templates/emerald/comment_post.tpl -->

<form action="{URL->ACTION}" method="post">
    {POST_VARS}
    <input type="hidden" name="action" value="post_comment_on_image" />
    <input type="hidden" name="image_number" value="{image_number}" />
    <input type="hidden" name="current_user" value="{USER->user_id}" />
    <input type="hidden" name="current_user_dn" value="{USER->display_name}" />

    <div class="generic">

        <small>

            {LANG->Message}:
            <div id="post-body">
                <textarea name="message" id="body" class="body" rows="8" cols="40">{MESSAGE->message}</textarea>
            </div>

        </small>

    </div>

    <div id="post-buttons">

        {HOOK "tpl_editor_buttons"}

      <input name="post" type="submit" value=" {LANG->mod_user_image_gallery->Post_Comment} " />

    </div>

</form>
<!-- END TEMPLATE mods/user_image_gallery/templates/emerald/comment_post.tpl -->
