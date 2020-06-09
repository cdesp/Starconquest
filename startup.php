<?php 

function is_https()
{
    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 1) {
        return true;
    } elseif (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
        return true;
    } else {
        return false;
    }
}

if (! is_https()) {
    header("location: https://{$_SERVER['HTTP_HOST']}");
    exit();
}


// echo $_SERVER["DOCUMENT_ROOT"];
// echo dirname(__FILE__) ;
 require_once(dirname(__FILE__) . "/config.php");

global $dbver, $starver;
$dbver = "0.0.1";
$starver=VERSION;
$projectstart="27/5/2013";
require_once("Core/common.php");
require_once("Core/planetutils.php");

if (!function_exists('base_url')) {
    function base_url($atRoot=false, $atCore=false, $parse=false)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http'; //2nd is http
            $hostname = $_SERVER['HTTP_HOST'];
            $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), null, PREG_SPLIT_NO_EMPTY);
            $core = $core[0];

            $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url = sprintf($tmplt, $http, $hostname, $end);
        } else {
            $base_url = 'http://localhost/';
        }

        if ($parse) {
            $base_url = parse_url($base_url);
            if (isset($base_url['path'])) {
                if ($base_url['path'] == '/') {
                    $base_url['path'] = '';
                }
            }
        }

        return $base_url;
    }
}




function login($username, $password)
{
    global $mysqli;
    
    $password = strip_tags($password);
    $username = strip_tags($username);
    $user1 = $mysqli->real_escape_string($username);
    $pass1 = md5($password);

    $q = "SELECT * FROM users where username = '".$user1."' AND password = '".$pass1."' LIMIT 1;";
    $result = query_exec($q);
    $dbarray = query_fetch_array($result);
    if ($dbarray['enabled'] == 1) {
        header("Location: index.php?msg=You are not enabled!");
    } else {
        if (query_num_rows($result) >0) {
            $id=$dbarray['id'];
            $_SESSION['id']=$id;
            $_SESSION['lang']=$dbarray['language'];
                
            $mtimet=mtimetn();
            $q = "UPDATE `users` SET `lastlogon` =  NOW( ) ,`ipaddr` = '".$_SERVER['REMOTE_ADDR']."' WHERE `id` =$id LIMIT 1 ;";
            query_exec($q);
                    
            //set uservers
            if ($dbarray['userver']!=VERSION) {
                $_SESSION['userver']=$dbarray['userver'];
            } else {
                $_SESSION['userver']=null;
            }
                    
            //ip control
            $ipqr=query_exec("SELECT `id` , `username` , `enabled` ,  `lastlogon` , `ipaddr` FROM `users` WHERE `ipaddr` = '".$_SERVER['REMOTE_ADDR']."'");
            if (query_num_rows($ipqr) >1) {
                $warntxt="Two users have the same ip!<br><table border='1'><tr><td>Username</td><td>Last log</td></tr>";
                while ($riga=query_fetch_array($ipqr)) {
                    $warntxt.="<tr><td>".$riga['username']."</td><td>".$riga['lastlogon']."</td></tr>";
                }
                $warntxt.="</table>";
                //	query_exec("INSERT INTO `".TB_PREFIX."warn` (`id` ,`text`) VALUES (NULL , '$warntxt');");
                adddebug($warntxt."<br>");
            }
            //echo '<BR>Login ok.';
                //logged sucess
        } else {
            header("Location: ?err=log&msg=Login Error probably wrong username and/or password");
        }
    }
}






function register()
{
    global $mysqli;
    //$fbid=filter_input(INPUT_POST, 'fbid');
    $user= $mysqli->real_escape_string(strip_tags(filter_input(INPUT_POST, 'user')));
    $pass=md5(filter_input(INPUT_POST, 'pass'));
    $mail=filter_input(INPUT_POST, 'email');
    $lang=filter_input(INPUT_POST, 'lang');
    
    
    
    if ($user=="" or $pass=="") {
        echo "Registration  is not valid! Fill all fields!";
    } else {
    
        //check validity of username
        $veryf="SELECT * FROM users WHERE username='".$user."'";
        $q_ver=query_exec($veryf);
        $ck_ver=query_num_rows($q_ver);
        //get unique id
        $qns=query_exec("SELECT id FROM users ORDER BY `id` DESC");
        if (query_num_rows($qns) >0) {
            $trows= query_fetch_array($qns);
            $id= $trows['id']+1;
        } else {
            $id= 1;
        }
    

        $mtimet=mtimetn();
    
        if ($ck_ver==1) {
            echo "username or password already exist(s)!";
        } else {
            $reg="INSERT INTO `users` (`id`, `name`, `username`, `password`,  `email`, `regdate`, `language`) VALUES ($id, '$user', '".$user."', '$pass', '$mail', '$mtimet', '$lang')";
            $q_reg=query_exec($reg);
            newuser($id);
            //Send validation email
            $to = $mail;
            $subject = "StarConquest validation email";
            $message = "<html><body>Hello! Please click bellow to validate your account.<BR>".
        // <BR>".base_url(true)."starconq/index.php?action=validate&mail=".$mail."&valno=".$mtimet."
        // <BR>
                
        "<BR>".base_url(true)."index.php?action=validate&mail=".$mail."&valno=".$mtimet."
		<BR>
		
		StarConquest Team.</body></html>
		";
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $from = "info@starconq.ddnsfree.com";
            $headers .= "From: " . $from;
            $headers .= "\r\n" .
                 "CC: cdesp72@gmail.com";
            //echo $to."<br>  ".$subject."<br>  ".$message."<br>  ".$headers;
            mail($to, $subject, $message, $headers);
            echo "Mail Sent.<BR> Don't forget to Check also on your spam folder.<BR>";
        
            // map sys registration begin \\
        }
    }
}


function validate()
{
    $validation = filter_input(INPUT_GET, 'valno');
    $mail =filter_input(INPUT_GET, 'mail');
    
    
    
    if ($mail=="" or $validation=="") {
        echo "Validation error!";
    } else {
    
        //check validity of username
        $veryf="SELECT * FROM users WHERE (email='".$mail."') and (regdate='".$validation."')";
        $q_ver=query_exec($veryf);
        $ck_ver=query_num_rows($q_ver);
        //		echo $veryf;

        if ($ck_ver==0) {
            echo "invalid validation email";
        } else {
            $reg="UPDATE `users` SET `enabled`=0";
            $q_reg=query_exec($reg);
            // 		   echo $reg;
            die("<BR>You have been registered!!. <BR><BR><a href='index.php'>Click here</a> to return to the login page.");
        }
    }
}



class starconq
{
    public $user_info;
    public $user_id;
    
    
    public function createmap($solsysno, $planpersys)
    {
    }
    
    
    public function main($uid)
    { //contructor, initialize all //loadinfo
        $this->user_info= query_fetch_array(query_exec("SELECT * FROM `users` WHERE `id` =".$uid." LIMIT 1;"));
        $this->user_id= $this->user_info['id'];
        
        //init funcions for resources, build, units and research
    }
}; // EC starconq
