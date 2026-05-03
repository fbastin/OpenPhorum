<!-- BEGIN TEMPLATE terms_of_service.tpl -->
<div class="nav">
    {IF URL->INDEX}<a class="icon icon-folder" href="{URL->INDEX}">{LANG->ForumList}</a>{/IF}
</div>

ESSAI

<div class="information">
    {LANG->TOS->Content}<br />
    {LANG->TOS->Version}
</div>

{IF LOGGEDIN}
// Give the possiblity to add entries
{/IF}
<!-- END TEMPLATE terms_of_service.tpl -->