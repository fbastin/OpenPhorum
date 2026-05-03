<!-- BEGIN TEMPLATE {TEMPLATE}/footer.tpl -->

</div><!-- end forum-content -->

    <div id="forum-footer-extras" class="forum-footer-extras">
      {IF LOGGEDIN}
        <a class="icon" style="background-image: url('mods/user_list/images/user_list.png');" href="{URL->USER_LIST->All}">Liste des inscrits</a>
      {/IF}
      {IF URL->TOS}<a class="icon icon-exclamation" href="{URL->TOS}">{LANG->TOS->Header}</a>{/IF}
    </div>

</main><!-- end content -->
</div><!-- end phorum -->

<?php
   $path = $_SERVER['DOCUMENT_ROOT'];
   $path .= "/footer_main.php";
   include_once($path);
?>

</div><!-- end wrapper -->

</body>
</html>
<!-- END TEMPLATE {TEMPLATE}/footer.tpl -->
