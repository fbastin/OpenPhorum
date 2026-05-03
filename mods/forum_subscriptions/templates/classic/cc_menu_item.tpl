{IF FORUM_SUBSCRIPTIONS_PANEL_ACTIVE}
  {VAR MENU_ITEM_CLASS 'class="phorum-current-page"'}
{ELSE}
  {VAR MENU_ITEM_CLASS ""}
{/IF}

<li>
  <a {MENU_ITEM_CLASS} href="{URL->CC_FORUM_SUBSCRIPTIONS}">
    {LANG->forum_subscriptions->ForumSubscriptions}
  </a>
</li>

