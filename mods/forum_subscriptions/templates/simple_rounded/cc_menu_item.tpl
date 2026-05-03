{IF FORUM_SUBSCRIPTIONS_PANEL_ACTIVE}
  {VAR MENU_ITEM_STYLE 'style="font-weight: bold"'}
{ELSE}
  {VAR MENU_ITEM_STYLE ""}
{/IF}
</table><table class="PhorumNavRowWrap" style="margin-top: -2px;" cellspacing="2px">
<tr>
  {INCLUDE "left_rounded_menu"}
  <td class="PhorumNavRowItem">
      <a class="PhorumNavLink" {MENU_ITEM_STYLE} href="{URL->CC_FORUM_SUBSCRIPTIONS}">{LANG->forum_subscriptions->ForumSubscriptions}</a>
  </td>
  {INCLUDE "right_rounded_menu"}
</tr>
