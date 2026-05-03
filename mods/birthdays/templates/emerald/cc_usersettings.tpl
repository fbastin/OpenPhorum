            {IF mod_birthdays}
                <dt>{LANG->mod_birthdays->CCcapture}:&nbsp;</dt>
                <dd>
                    {MOD_BIRTHDAYS_DAY} /
                    {MOD_BIRTHDAYS_MONTH} /
                    {MOD_BIRTHDAYS_YEAR} &nbsp; &nbsp; &nbsp;
                    <label for="mod_birthdays_age">{LANG->mod_birthdays->ShowAge}</label> 
                    <input type="checkbox" name="mod_birthdays_age" id="mod_birthdays_age" value="1" {MOD_BIRTHDAYS_AGE} >
                </dd>
            {/IF}
