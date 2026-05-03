<!-- BEGIN TEMPLATE profile.tpl -->
<div class="nav">
    {IF URL->INDEX}<a class="icon icon-folder" href="{URL->INDEX}">{LANG->ForumList}</a>{/IF}
    {IF FORUM_ID}
        <a class="icon icon-list" href="{URL->LIST}">{LANG->MessageList}</a>
    {/IF}
</div>

<div id="profile">

    <div class="generic">

        {IF PROFILE->user_avatar}
          <img src="{PROFILE->user_avatar}" alt="avatar"
           {IF PROFILE->user_avatar_w}
             style="width:{PROFILE->user_avatar_w}px;
                    height:{PROFILE->user_avatar_h}px"
           {/IF} align="right" />
        {/IF}

        <div class="icon-user">
            {PROFILE->display_name}
            <small>
              {IF LOGGEDIN}
                {IF ENABLE_PM}
                        {IF PROFILE->is_buddy} ({LANG->Buddy}){/IF}
                        [ <a href="{PROFILE->URL->PM}">{LANG->SendPM}</a> ]
                        {IF NOT PROFILE->is_buddy}
                            [ <a href="{PROFILE->URL->ADD_BUDDY}">{LANG->BuddyAdd}</a> ]
                        {/IF}
                {/IF}
              {/IF}
              [ <a href="{PROFILE->URL->SEARCH}">{LANG->ShowPosts}</a> ]
            </small>
        </div>

        <dl>

            <dt>{LANG->Email}:</dt>
            <dd>{PROFILE->email}</dd>

            {IF PROFILE->real_name}
                <dt>{LANG->RealName}:</dt>
                <dd>{PROFILE->real_name}</dd>
            {/IF}

            {IF PROFILE->posts}
                <dt>{LANG->Posts}:&nbsp;</dt>
                <dd>{PROFILE->posts}</dd>
            {/IF}
            {IF PROFILE->date_added}
                <dt>{LANG->DateReg}:&nbsp;</dt>
                <dd>{PROFILE->date_added}</dd>
            {/IF}
            {IF PROFILE->date_last_active}
                <dt>{LANG->DateActive}:&nbsp;</dt>
                <dd>{PROFILE->date_last_active}</dd>
            {/IF}

	    {IF PROFILE->URL->user_image_gallery}
	        <dt><a href="{PROFILE->URL->user_image_gallery}">Galerie personnelle</a></dt><dd></dd>
            {/IF}


  {IF PROFILE->mod_google_maps->city}
                <dt>{LANG->mod_google_maps->Location}:&nbsp;</dt>
                <dd>
    {PROFILE->mod_google_maps->country},
    {PROFILE->mod_google_maps->city}</dd>
  {/IF}
        </dl>

    </div>

</div>
<!-- END TEMPLATE profile.tpl -->
