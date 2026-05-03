{IF ERROR}<div class="attention">{ERROR}</div>{/IF}
{IF OKMSG}<div class="information">{OKMSG}</div>{/IF}
{IF MESSAGES}
  {LOOP MESSAGES}
    <div class="{MESSAGES->type}">{MESSAGES->message}</div>
  {/LOOP MESSAGES}
{/IF}

    <table cellspacing="0" class="list" style="width:100%">
      <tr>
        <th align="center" style="white-space:nowrap" colspan="{mod_user_image_gallery->display_columns}">
          {LANG->Preview} ({LANG->mod_user_image_gallery->Thumbnail})
        </th>
      </tr>

      {VAR CURRCOL 0}
      {LOOP FILES}
      {IF CURRCOL 0}
        <tr>
        <?php $second_row='<tr>'; ?>
      {/IF}
          <td style="vertical-align:middle;text-align:center;" class="row1">
            <a href="{FILES->link}"><img src="{FILES->url}" {FILES->adjustment} border=0 /></a>
          </td>
          <?php ob_start(); ?>
          <td style="vertical-align:top;text-align:center;" class="row2">
              <a href="{FILES->link}">{FILES->title}</a><br/>
              {FILES->filesize}{LANG->mod_user_image_gallery->_bytes}
              {IF FILES->dimensions}<br />({FILES->dimensions}){/IF}
          </td>
          <?php $second_row .= ob_get_contents(); ob_end_clean(); ?>
      {! VAR CURRCOL CURRCOL+1}
      <?php $PHORUM['DATA']['CURRCOL'] = $PHORUM['DATA']['CURRCOL']+1; ?>
      {IF CURRCOL mod_user_image_gallery->display_columns}
        </tr>
        <?php print $second_row.'</tr>'; $second_row=''; ?>
        {VAR CURRCOL 0}
      {/IF}
      {/LOOP FILES}

      {IF NOT CURRCOL 0}
          <td colspan="<?php echo ( $PHORUM['DATA']['mod_user_image_gallery']['display_columns'] - $PHORUM['DATA']['CURRCOL'] ); ?>" class="row1">
            &nbsp;
          </td>
        </tr>
        <?php print $second_row; ?>
          <td colspan="<?php echo ( $PHORUM['DATA']['mod_user_image_gallery']['display_columns'] - $PHORUM['DATA']['CURRCOL'] ); ?>" class="row2">
            &nbsp;
          </td>
        </tr>
      {/IF}

    </table>



<div class="nav">
    {IF MULTIPLE_PAGES}
      {INCLUDE 'paging'}
    {/IF}
</div>

<span style="clear:both;">&nbsp;</span>

      {! search box}
      {INCLUDE "user_image_gallery::search_galleries"}


