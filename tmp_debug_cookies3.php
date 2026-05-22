<?php
$cookie='D:/XAMPP/htdocs/DatabaseTKM/tmp_cookie_debug4.txt'; @unlink($cookie);
$ch=curl_init();curl_setopt_array($ch,[CURLOPT_URL=>'http://localhost/DatabaseTKM/Auth',CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HEADER=>false,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,CURLOPT_ENCODING=>'',CURLOPT_COOKIEFILE=>'',CURLOPT_COOKIEJAR=>$cookie]);curl_exec($ch);curl_close($ch);echo file_exists($cookie)?"exists\n":"no\n";echo file_get_contents($cookie);
