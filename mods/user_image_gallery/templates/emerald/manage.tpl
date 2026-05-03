{IF ERROR}<div class="attention">{ERROR}</div>{/IF}
{IF OKMSG}<div class="information">{OKMSG}</div>{/IF}
{IF MESSAGES}
  {LOOP MESSAGES}
    <div class="{MESSAGES->type}">{MESSAGES->message}</div>
  {/LOOP MESSAGES}
{/IF}

<a href="{IMAGE->url}" target="_blank"><img src="{IMAGE->url}" border=0></a><br><br>

<?php /*
Info available:
  IMAGE->adjustment
  IMAGE->dimensions
  IMAGE->filesize
  IMAGE->raw_dateadded
  IMAGE->dateadded
  IMAGE->url

also (but not as useful):
  IMAGE->file_id
  IMAGE->user_id
  IMAGE->filename
  IMAGE->add_datetime
  IMAGE->message_id
  IMAGE->width
  IMAGE->height
  IMAGE->owner
*/ ?>

<br />
<table>
  <tr>
    <td width="33%" valign="top">
      <div class="generic">
        <form action="{URL->ACTION}" method="post">
        {POST_VARS}
        <input type="hidden" name="action" value="manage_edit">
        <input type="hidden" name="screen" value="manage">
        <input type="hidden" name="file_id" value="{IMAGE->file_id}">
  
        {IF additional 'watermark'}
          Text to use for watermark:<br>
          <input type="text" name="watermark_text" value=""><br />
          Color: {COLOR_PULLDOWN}<br />
          Font: {FONT_PULLDOWN}<br />
          <input type="submit" name="watermark2" value="Go"><br />
        {ELSE}
          {LANG->mod_user_image_gallery->Image_alteration_title}<br />
          <small>{LANG->mod_user_image_gallery->Image_alteration_warning}</small><br />
          <input type="submit" name="rotate90" value="{LANG->mod_user_image_gallery->Rotate_Right}"><br />
          <input type="submit" name="rotate270" value="{LANG->mod_user_image_gallery->Rotate_Left}"><br />
          <input type="submit" name="rotate180" value="{LANG->mod_user_image_gallery->Rotate_180}"><br />
          <input type="submit" name="watermark1" value="{LANG->mod_user_image_gallery->Watermark}"><br />
        {/IF}

        <br>
        </form>
      </div>
    </td>
    <td width="33%" valign="top">
      {LANG->mod_user_image_gallery->label_Filename}: {IMAGE->filename}<br />
      {LANG->mod_user_image_gallery->label_Dimensions}: {IMAGE->width} x {IMAGE->height}<br />
      {LANG->mod_user_image_gallery->label_Size}: {IMAGE->filesize} bytes<br />
      {LANG->mod_user_image_gallery->label_Date_added}: {IMAGE->dateadded}<br />
      {LANG->mod_user_image_gallery->label_Date_modified}: {IMAGE->moddate}<br />
    </td>
    <td width="33%" valign="top">
      {IF additional 'watermark'}
        {! this space intentionally left blank}
      {ELSE}
        <div class="generic">
          {LANG->mod_user_image_gallery->Re_upload_image_title}<br />
          <form action="{URL->ACTION}" method="post" enctype="multipart/form-data">
          {POST_VARS}
          <input type="hidden" name="action" value="manage_edit">
          <input type="hidden" name="screen" value="manage">
          <input type="hidden" name="file_id" value="{IMAGE->file_id}">
          <input type="file" name="newfile1" size="50" class="uploadblank" /><br />
          <input type="submit" name="re_upload" value="{LANG->mod_user_image_gallery->Upload}"><br />
          <br />
          </form>
        </div class="generic">
      {/IF}
    </td>

  </tr>
</table>

<a href="{URL->return}">{LANG->mod_user_image_gallery->Back_to_Control_Panel}</a><br><br>

