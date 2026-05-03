{IF ERROR}<div class="attention">{ERROR}</div>{/IF}
{IF OKMSG}<div class="information">{OKMSG}</div>{/IF}
{IF MESSAGES}
  {LOOP MESSAGES}
    <div class="{MESSAGES->type}">{MESSAGES->message}</div>
  {/LOOP MESSAGES}
{/IF}

<img src="{IMAGE->url}" border=0><br><br>

<br />
<table>
  <tr>
    <td valign="top">
      {IF IMAGE->title}{LANG->mod_user_image_gallery->label_Title}: {IMAGE->title}<br />{/IF}
      {IF IMAGE->description}<br />{LANG->mod_user_image_gallery->label_Description}:<br />{IMAGE->description}<br /><br />{/IF}
      {IF IMAGE->keywords}{LANG->mod_user_image_gallery->label_Keywords}: {IMAGE->keywords}<br />{/IF}
      {LANG->mod_user_image_gallery->label_Filename}: {IMAGE->filename}<br />
      {LANG->mod_user_image_gallery->label_Dimensions}: {IMAGE->width} x {IMAGE->height}<br />
      {LANG->mod_user_image_gallery->label_Size}: {IMAGE->filesize} bytes<br />
      {LANG->mod_user_image_gallery->label_Date_added}: {IMAGE->dateadded}<br />
      {LANG->mod_user_image_gallery->label_Date_modified}: {IMAGE->moddate}<br />
      {LANG->mod_user_image_gallery->label_Owner}: {IMAGE->owner}<br />
      <br />
      {INCLUDE "user_image_gallery::report_image"}

      <br><br><a href="{URL->BACK}">{BACK_CAPTION}</a><br><br>

      {! search box}
      {INCLUDE "user_image_gallery::search_galleries"}

    </td>

    {IF mod_user_image_gallery->allow_comments}

    <td valign="top">
      {LANG->mod_user_image_gallery->User_comments}:<br />

      {! display any comments so far}
      {LOOP COMMENTS}
        {LANG->mod_user_image_gallery->Posted_by} {COMMENTS->sender_name} on {COMMENTS->posted_date}<br />
        {COMMENTS->message}<br />
        {! also available: COMMENTS->sender_id}
        {! if user is looking at his/her own gallery, display comment delete button}
        {IF delete_button}
          <form action="{URL->ACTION}" method="post">
            {POST_VARS}
            <input type="hidden" name="action" value="delete_comment_on_image" />
            <input type="hidden" name="comment_number" value="{COMMENTS->comment_number}" />
            <input type="hidden" name="image_number" value="{image_number}" />
            <input type="hidden" name="current_user" value="{USER->user_id}" />
            <input name="delete" type="submit" value=" {LANG->mod_user_image_gallery->Delete_Comment} " />
          </form>
        {/IF}
        <hr />
      {/LOOP COMMENTS}

      {! display post box for new comments, if user is logged in }
      {IF loggedin}
        {INCLUDE "user_image_gallery::comment_post"}
      {/IF}

    </td>

    {/IF}

  </tr>
</table>



