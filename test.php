<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo dirname(__FILE__)."<br>";
$fn= dirname(__FILE__).'/tmp/test.txt';
if (is_dir($fn))
    echo "Name is dir<br>";
else
    echo "Name is not dir<br>";
echo $fn."<br>";

$fp =fopen($fn , 'w');
if ($fp==false)
    echo "File not created.<br>";
//echo "read->".fread($fp,6)."<br>";

fwrite($fp, 'Cats chase mice');
if (fclose($fp))
  echo "test ok!!!";
else     
    echo "test failed!!!";

 echo "<br>";
 if ( phpinfo()==false)
     echo "PHPinfo failed";

 echo "<BR>Version:".phpversion ();
 echo "<br>GI:".$_ENV['GATEWAY_INTERFACE']." - ".$_SERVER['GATEWAY_INTERFACE'];

?>