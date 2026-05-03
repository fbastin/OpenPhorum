<?php	
		// you can delete this script, I'm just using it to test my regex - /\dam

		//  try and detect raw email addresses
		$string = "General support*sad*((((test@hotmail.com )))) (((((test@hotmail.com )))) blah blah test@yahoo.com test@yahoo.co.uk\n blah\n";
		$pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
		// preg_match_all returns an associative array
		preg_match_all($pattern, $string, $emailmatches);
		$num_raw_email_addresses = count($emailmatches[0]);
		echo "emails detected = $num_raw_email_addresses";

?>