<?php
$cookie='D:/XAMPP/htdocs/DatabaseTKM/tmp_cookie_debug2.txt'; @unlink($cookie);
function req($m,$u,$c,$p=[]){$ch=curl_init();curl_setopt_array($ch,[CURLOPT_URL=>$u,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_COOKIEJAR=>$c,CURLOPT_COOKIEFILE=>$c,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,CURLOPT_ENCODING=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>60]);if($m==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($p));}$b=(string)curl_exec($ch);$s=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$e=curl_error($ch);curl_close($ch);return [$s,$b,$e];}
[$s0,$b0,$e0]=req('GET','http://localhost/DatabaseTKM/Auth',$cookie,[]);
[$s1,$b1,$e1]=req('POST','http://localhost/DatabaseTKM/Auth',$cookie,['username'=>'admin','pass'=>'admin787898-']);
[$s2,$b2,$e2]=req('GET','http://localhost/DatabaseTKM/Dashboard',$cookie,[]);
echo "s0=$s0 len0=".strlen($b0)."\n";
echo "s1=$s1 len1=".strlen($b1)."\n";
echo "s2=$s2 len2=".strlen($b2)."\n";
echo "title0=".(preg_match('/<title>(.*?)<\/title>/is',$b0,$m0)?trim($m0[1]):'-')."\n";
echo "title1=".(preg_match('/<title>(.*?)<\/title>/is',$b1,$m1)?trim($m1[1]):'-')."\n";
echo "title2=".(preg_match('/<title>(.*?)<\/title>/is',$b2,$m2)?trim($m2[1]):'-')."\n";
