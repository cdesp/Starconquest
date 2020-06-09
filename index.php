<?php
$secret= true;
$head=""; $body=""; $blol="";
if (!file_exists("config.php")) {
    header("Location: error.php");
}
require_once "Core/myutils.php";
//new session
session_start();
header('Content-Type:text/html; charset=UTF-8');

//session expiration check
if (myisset(getsessionvar('LAST_ACTIVITY'))) {
    adddebugval("Timeout", time() - $_SESSION['LAST_ACTIVITY']);
    if ((time() - $_SESSION['LAST_ACTIVITY']) > (4*1800)) { //2hours
        // last request was more than 30 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
        header("Location: index.php");
        exit;
    }
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
global $onclient;

$onclient = true;

ob_start();
require_once "startup.php";
require_once "Core/galaxyutils.php";

db_connect();
global $pg,$starver;
initgalaxy();

$inppg=filter_input(INPUT_GET, 'pg');
if (myisset($inppg)) {
    $pg=$inppg;
} elseif (myisset(getsessionvar('curpage'))) {
     $pg=$_SESSION['curpage'];
 } else {
     $pg='map';
 }

//echo $pg.'<br>';die('');
//Language
$inplang= filter_input(INPUT_GET, 'lang');
if (myisset($inplang)) {
    include("./language/".$inplang.".php");
    $glang=$inplang;
    $lkrl="?lang=".$inplang;
} else {
    if (myisset(getsessionvar('lang'))) {
        include("./language/".$_SESSION['lang'].".php");
    } else {
        include("./language/".LANG.".php");
    }
    $glang=LANG;
}

//all post vars
/*
foreach ($_POST as $key => $value) {
    echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
}*/

$inpreg=filter_input(INPUT_POST, 'register_x');
$inplogin=filter_input(INPUT_POST, 'login_x');
$inpactionREQ=getrequestvar('action');
$inpactionGET=filter_input(INPUT_GET, 'action');
$inpactionPOST=filter_input(INPUT_POST, 'action');

//echo "[$inpreg]<BR>";
//echo "[$inplogin]<BR>";
//echo "[$inpactionREQ]<BR>";
//echo "[$inpactionGET]<BR>";
$act=null;
if (myisset($inpreg)) {
    $pg='register';
} elseif (myisset($inplogin)) {
    $act='login';
} elseif (myisset($inpactionREQ)) {
    $act=$inpactionREQ;
} elseif (myisset($inpactionGET)) {
      $act=$inpactionGET;
} elseif (myisset($inpactionPOST)) {
      $act=$inpactionPOST;
}

 
//echo $act." - ".$pg;
 if (myisset($act)) {
     if ($act=='logout' && $pg=='register') {
         $act='';
     }
 }
//Execute Action
if (myisset($act)) {
    switch ($act) {
        case "login":
            login(filter_input(INPUT_POST, 'user'), filter_input(INPUT_POST, 'pass'));
        break;
        case "register":
            register();
        break;
        case "validate":
            validate();
            // no break
        case "logout":
            session_destroy();
            session_start();
            $pg="index";
        break;
    }
}
//date_default_timezone_set('Europe/Athens');
 $dat = date("l, F j, Y h:i:s a", mtimetn());
 if (myisset(getsessionvar('userver')) and (getsessionvar('userver')!=null)) {
     $myver='<span style="color:red"><strong>'.$_SESSION['userver'].'</strong></span>';
 } else {
     $myver=$starver;
 }
  addtopoutput("Star Conquest Alpha version $myver (Start:$projectstart), Server time: $dat", "");
  
if (myisset(getsessionvar('id'))) {
    addtopoutput(", <a href='?action=logout'> Logout</a>");
    if (!myisset($pg)) {
        $pg="main";
    }
    $me= new starconq($_SESSION['id']);

    //Query from other pages
    $t=$_SESSION['id'];
    adddebug("Session ID set for user:[$t]<BR>");
} else {
    if ($pg!="register") {
        //logout
        session_destroy();
        session_start();
        $pg="index";
        adddebug("FORCE LOGOUT");
    }
}
    

if (!isset($me) and $pg!="register") {
    $pg= "index";
}

$inpmsg=filter_input(INPUT_GET, 'msg');
  if (myisset($inpmsg)) {
      $msg = $inpmsg;
      //$url=$_SERVER['QUERY_STRING'];
      $url=curPageURL();
      $url1=$url;
      $url=remove_querystring_var($url, "action");
      $url2=$url;
      $url=remove_querystring_var($url, "msg");
      $url3=$url;

      echo  $pg.' <script> alert("'.$msg.'");
	 location.replace("'.$url.'");
	  </script>';

    
     
      // header("Location: index.php");
      die('');
  }


if (isset($me) and myisset($pg) and $pg!="index") {
    //	if (!myisset($_SESSION['selplanet'])) {
    //		$_SESSION['selplanet']=getusercapitol();
    //	}
  
    if (!includecorepage($pg)) {
        adddebug("PAGE DOESN'T EXIST!");
    } else {
        $_SESSION['curpage']=$pg;
        init_page();
    }
}




    adddebug('Page='.$pg);
    //if (myisset($_SESSION['dbg']))
         //adddebug('<br><b>'.$_SESSION['dbg'].'</b>');
    include("template/index.php");
  
  ob_end_flush();
