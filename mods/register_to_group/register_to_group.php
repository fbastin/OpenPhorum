<?php 

function phorum_mod_register_to_group_after_register($userdata) {
	global $PHORUM;
	
	if(isset($PHORUM['mod_regtogroup']) && is_array($PHORUM['mod_regtogroup'])) {
		
		
		phorum_api_user_save_groups($userdata['user_id'],$PHORUM['mod_regtogroup']);
	}
	
	return $userdata;
}

?>