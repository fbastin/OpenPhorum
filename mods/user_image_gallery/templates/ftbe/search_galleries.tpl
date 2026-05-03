      {! search box}
      <div id="search_galleries">
          <form action="{URL->ACTION}" method="post">
              {POST_VARS}
              <input type="hidden" name="action" value="search">
              <input type="hidden" name="screen" value="search">
              Keyword search all galleries:<br />
              <input type="text" name="search" size="30" value="" />
              <input type="submit" value="{LANG->Search}" /><br />
          </form>
      </div id="search_gallery">

