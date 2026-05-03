{IF ERROR}<div class="attention">{ERROR}</div>{/IF}
{IF OKMSG}<div class="information">{OKMSG}</div>{/IF}
{IF MESSAGES}
  {LOOP MESSAGES}
    <div class="{MESSAGES->type}">{MESSAGES->message}</div>
  {/LOOP MESSAGES}
{/IF}

<a href="{IMAGE->url}" target="_blank"><img src="{IMAGE->url}" border=0></a><br><br>

<br />
<table>
  <tr>
    <td width="50%" valign="top">
      <form action="{URL->ACTION}" method="post">
      {POST_VARS}
      <input type="hidden" name="action" value="info_edit">
      <input type="hidden" name="screen" value="info">
      <input type="hidden" name="file_id" value="{IMAGE->file_id}">
      {LANG->mod_user_image_gallery->label_Title}:<br>
      <input type="text" name="title" value="{IMAGE->title}" class="input_text"><br>
      <br>
      {LANG->mod_user_image_gallery->label_Description}:<br>
      <textarea name="description" class="input_textarea">{IMAGE->description}</textarea><br>
      <br>
      {LANG->mod_user_image_gallery->label_Keywords}:<br>
      <input type="text" name="keywords" value="{IMAGE->keywords}" class="input_text"><br>
      <font style="size: 5pt;">Separate with commas</font><br>
      <br>
      <input type="submit" value="{LANG->SaveChanges}">
      </form>
    </td>
    <td width="50%" valign="top">
      {IF IMAGE->title}{LANG->mod_user_image_gallery->label_Title}: {IMAGE->title}<br />{/IF}
      {IF IMAGE->description}<br />{LANG->mod_user_image_gallery->label_Description}:<br />{IMAGE->description}<br /><br />{/IF}
      {IF IMAGE->keywords}{LANG->mod_user_image_gallery->label_Keywords}: {IMAGE->keywords}<br />{/IF}
      {LANG->mod_user_image_gallery->label_Filename}: {IMAGE->filename}<br />
      {LANG->mod_user_image_gallery->label_Dimensions}: {IMAGE->width} x {IMAGE->height}<br />
      {LANG->mod_user_image_gallery->label_Size}: {IMAGE->filesize} bytes<br />
      {LANG->mod_user_image_gallery->label_Date_added}: {IMAGE->dateadded}<br />
      {LANG->mod_user_image_gallery->label_Date_modified}: {IMAGE->moddate}<br />
    </td>
  </tr>
  <tr>
    <td colspan=2>
      <br />
      URL:<br />
      <span style="font-size: 8pt; white-space: nowrap;">{IMAGE->url}</span><br />
    </td>
  </tr>
</table>

<a href="{URL->return}">{LANG->mod_user_image_gallery->Back_to_Control_Panel}</a><br><br>




<?php if(false): ?>
  {LANG->mod_user_image_gallery->Total_files}: {TOTAL_FILES} ({LANG->mod_user_image_gallery->limit_of_}{mod_user_image_gallery->max_images})<br />
  {LANG->mod_user_image_gallery->Total_file_size}: {TOTAL_FILE_SIZE} ({LANG->mod_user_image_gallery->limit_of_}{mod_user_image_gallery->max_total_filesize}{LANG->mod_user_image_gallery->_KB})<br />
<?php endif; ?>


