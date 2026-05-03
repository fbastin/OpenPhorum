<!-- start /mods/announcements/templates/bitmapworld/announcements.tpl -->
<table cellspacing=0 class="list announcements">
    <tr>
        <th align="left" colspan="2">
            {LANG->Announcements}
        </th>
        <th align="left" nowrap="nowrap" class="lastcol">{LANG->LastPost}</th>
    </tr>

    {LOOP ANNOUNCEMENTS}

        {IF ANNOUNCEMENTS->new}
          {VAR icon "flag_red"}
          {VAR read_url ANNOUNCEMENTS->URL->NEWPOST}
        {ELSE}
          {VAR icon "information"}
          {VAR read_url ANNOUNCEMENTS->URL->READ}
        {/IF}
        {VAR title LANG->Announcement}

        <tr>
            <td width="1%"><a href="{read_url}" title="{title}"><img src="{URL->TEMPLATE}/images/{icon}.png" border="0" alt="{title}" /></a></td>
            <td width="80%"><span class="h4"><a href="{ANNOUNCEMENTS->URL->READ}" title="{title}">{ANNOUNCEMENTS->subject}</a></span></td>
            <td width="19%" nowrap="nowrap" class="lastcol">{ANNOUNCEMENTS->lastpost}</td>
        </tr>
  {/LOOP ANNOUNCEMENTS}
</table>
<!-- end ~/announcements.tpl -->
