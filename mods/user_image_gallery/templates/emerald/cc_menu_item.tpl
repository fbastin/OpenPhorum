{IF IMAGE_GALLERY_PANEL_ACTIVE}
  {VAR MENU_ITEM_CLASS 'class="current"'}
{ELSE}
  {VAR MENU_ITEM_CLASS ""}
{/IF}

<li>
  <a {MENU_ITEM_CLASS} href="{URL->CC_IMAGE_GALLERY}">
    {LANG->mod_user_image_gallery->CCMenuItem}
  </a>
</li>

