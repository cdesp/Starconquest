<?php 
 require 'startup.php';

	$mail = 'cdesp72@gmail.com';
	$mtimet = 1294857;

		$to = 'cdesp72@gmail.com';
		$subject = "StarConquest validation email";
		$message = "Hello! Please click bellow to validate your account.<BR>
		<BR>
    	http://cdesp.homeip.net/starconq/index.php?action=validate&mail=".$mail."&valno=".$mtimet."
		<BR>
		StarConquest Team.
		";
		$from = "cdesp72@gmail.com";
		$headers = "From:" . $from;
		dspsendmail($to,$subject,$message);
		
		echo "<BR><BR>";
		echo "-------------------------------------";
		echo "Mail Sent.<BR>";		
		echo $to.'<BR>';
		echo $headers.'<BR>';
		echo $subject.'<BR>';
		echo $message.'<BR>';
?>
