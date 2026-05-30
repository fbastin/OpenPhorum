<!-- BEGIN TEMPLATE pm_list.tpl -->
<div class="nav" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
    <div>{INCLUDE "paging"}</div>
    <div>
        <input type="text" id="pm_live_search" placeholder="Filtrage en direct..." style="padding: 4px; border: 1px solid #ccc; border-radius: 4px; width: 250px;" />
    </div>
</div>

<script type="text/javascript">
document.getElementById('pm_live_search').addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#phorum-pm-list table.list tr:not(:first-child)');
    
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        if (text.indexOf(filter) > -1) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

<form action="{URL->ACTION}" method="post" id="phorum-pm-list">
    {POST_VARS}
    <input type="hidden" name="action" value="list" />
    <input type="hidden" name="folder_id" value="{FOLDER_ID}" />
    {IF FOLDER_IS_INCOMING}
        {INCLUDE "pm_list_incoming"}
    {ELSE}
        {INCLUDE "pm_list_outgoing"}
    {/IF}
    <!-- CONTINUE TEMPLATE list.tpl -->
</form>
<!-- END TEMPLATE pm_list.tpl -->
