<?php

// Set the default timezone to use. Available as of PHP 5.1
date_default_timezone_set('Europe/Athens');

require_once(dirname(__FILE__) . "/../config.php");
include_once "myutils.php";
$mysqli = db_connect();

function initbgvals()
{
    global $bgwidth, $bgheight, $bgoffsetx, $bgoffsety, $bgquadrh, $bgquadrv, $redzonex, $redzonewidth, $quadsizex, $quadsizey;

    $bgwidth = 600;
    $bgheight = 500;
    $bgoffsetx = 25;
    $bgoffsety = 40;
    $bgwidth -= $bgoffsetx;
    $bgheight -= $bgoffsety;
    $bgquadrh = 20;  //horizontal grid
    $bgquadrv = 15;  //vertical grid
    $redzonex = floor($bgquadrh / 2);
    $redzonewidth = 1;
    $quadsizex = floor($bgwidth / $bgquadrh);
    $quadsizey = floor($bgheight / $bgquadrv);

    $bgwidth = $bgoffsetx + $bgquadrh * $quadsizex;
    $bgheight = $bgoffsety + $bgquadrv * $quadsizey;
}

function db_connect()
{  //connection to db from config
    global $mysqli;

    $mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    /* check connection */

    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
    }

    query_exec('SET NAMES UTF8');
}

function query_insert_id()
{
    global $mysqli;
    return mysqli_insert_id($mysqli);
}

function query_exec($qry)
{
    global $mysqli;
    $res = $mysqli->query($qry);
    if (!$res) {
        adddebug("ERROR. QUERY NOT EXECUTED!!!<BR>");
        addbottomoutput("ERROR-->[" . $qry . "]<BR>");
    }

    // echo "QR=".$qry."<BR>";
    return $res;
}

function query_num_rows($qres)
{
    return $qres->num_rows;
}

function query_fetch_array($qres)
{
    return $qres->fetch_array();
}

function getfieldfromdbtable($dbtable, $field, $keyname, $key)
{
    $qr = "select `$field` from `$dbtable` where `$keyname`=$key";
    if (executequery($qr, $qres, $qrcnt) && $qrcnt > 0) {
        $resarr = query_fetch_array($qres);
//      addbottomoutput($qr."<BR>");
        return $resarr["$field"];
    } else {
        return false;
    }
}

function gettablenextid($tbname, $tbkey)
{
    $quer = "SELECT $tbkey FROM $tbname ORDER BY  `" . $tbkey . '` DESC LIMIT 1';
    $qres = query_exec($quer);
    //echo $quer;
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        $id = $trows[$tbkey] + 1;
    } else {
        $id = 1;
    }

    return $id;
}

function executequery($qr, &$tqres, &$tqrcnt)
{
    $tqres = query_exec($qr);
    if (!$tqres) {
        addbottomoutput("ERROR-->(" . $qr . ")<BR>");
        $tqrcnt = 0;
        return false;
    } else {
        $tqrcnt = query_num_rows($tqres);
        return true;
    }
}

function decreaseversion(&$ver)
{
    //	adddebugval('ver',$ver);
    $verarr = explode(".", $ver);
    $version = $verarr[0];
    $subvers = $verarr[1];
    $subsubv = $verarr[2];
    //	adddebugval('ver',$version);
    //	adddebugval('subver',$subvers);
    //	adddebugval('subsubver',$subsubv);

    if ($subsubv > 0 or $subvers > 0 or $version > 0) {
        $subsubv--;
        if ($subsubv < 0) {
            $subsubv = 0;
            $subvers--;
        }
        if ($subvers < 0) {
            $subvers = 0;
            $version--;
        }
        if ($version < 0) {
            $version = 0;
        }
    }

    $ver = $version . "." . $subvers . "." . $subsubv;
    //	adddebugval('ver',$ver);
    return $version == 0 and $subvers == 0 and $subsubv == 0;
}

function includeversionedfile($page, $isajax = false, $predir = '', $ext = 'php')
{

    //if ($isajax) adddebug ("AJAX icp<br>");

    $userver = $_SESSION['userver'];
    if ($userver != null) {
        $extver = $userver;
    } //priority 1
    else {
        $extver = VERSION;
    }   //standard version

        
    // adddebugval('looking for',"$predir$page");

    $zeroed = false;
    if ($extver != '') {
        do {
            $found = file_exists("$predir$page.$extver.$ext");
            if (!$found) {
                $zeroed = decreaseversion($extver);
            }
            // else adddebug('found<br>');
        } while (!$found and!$zeroed);
    }
    //if ($zeroed) adddebug('zeroed<br>');

    if (!$zeroed and file_exists("$predir$page.$extver.$ext")) {
        //  adddebugval('File to load',"$predir$page.$extver.$ext");
        return "$predir$page.$extver.$ext";
        // include_once("$predir$page.$extver.$ext");
        // return true;
    } elseif (file_exists("$predir$page.$ext")) {
        //include_once("$predir$page.$ext");
        //adddebugval('file loaded',"$predir$page.$ext");
        return "$predir$page.$ext";
        // return true;
    } elseif (!$isajax) {
        // adddebug('FORCE ajax<br>');
        return includeversionedfile($page, true, '', $ext);
    } else {
        return "";
    }
}

function includecorepage($page, $isajax = false)
{
    if (!$isajax) {
        $predir = "Core/";
    } else {
        // adddebug("AJAX icp<br>");
        $predir = "";
    }

    $pgtoload = includeversionedfile($page, $isajax, $predir);
    if ($pgtoload == '') {
        adddebug("$predir$page not loaded!<br>");
        return false;
    } else {
        include_once $pgtoload;
        adddebug("$pgtoload LOADED!<br>");
        //load css if exists
        if (!$isajax) {
            $predir = "CSS/";
        } else {
            //  adddebug("AJAX CSS icp<br>");
            $predir = "";
        }
        $csstoload = includeversionedfile($page, $isajax, $predir, 'css');
        if ($csstoload != '') {
            // adddebug("CSS=[$csstoload]<br>");
            $ifile = " <link type='text/css' rel='stylesheet' href='$csstoload' />";
            addincludefile($ifile); //check for  AJAX
        } else {
            adddebug("CSS NOT LOADED=[$predir\\$page]<br>");
        }
        return true;
    }
}

function includecorepage2($page, $isajax = false)
{
    $userver = $_SESSION['userver'];
    if ($userver != null) {
        $extver = $userver;
    } //priority 1
    else {
        $extver = VERSION;
    }   //standard version

    if (!$isajax) {
        $predir = "Core/";
    } else {
        $predir = "";
    }

    // adddebugval('looking for',"$predir$page");

    $zeroed = false;
    if ($extver != '') {
        do {
            $found = file_exists("$predir$page.$extver.php");
            if (!$found) {
                $zeroed = decreaseversion($extver);
            } else ;
            //	  adddebug('found<br>');
        } while (!$found and!$zeroed);
    }
    //if ($zeroed) adddebug('zeroed<br>');

    if (!$zeroed and file_exists("$predir$page.$extver.php")) {
        //	  adddebugval('file',"$predir$page.$extver.php");
        include_once("$predir$page.$extver.php");
        return true;
    } elseif (file_exists("$predir$page.php")) {
        include_once("$predir$page.php");
        //		adddebugval('file loaded',"$predir$page.php");
        return true;
    } elseif (!$isajax) {
        //			  adddebug('with ajax<br>');
        includecorepage($page, true);
    } else {
        return false;
    }
}

function includeajaxpage($page)
{
    return includecorepage($page, true);
}

function activityoccur()
{
    $_SESSION['LAST_ACTIVITY'] = time();
}

//Calculates the difference between $start and $s, returns a formatted string Xd Yh Zm As, e.g. 15d 23h 54m 31s. Empty sections will be stripped, returning 12d 4s, not 12d 0h 0m 4s.
function time_diff_conv($start, $s)
{
    $t = array(//suffixes
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
    );
    $s = abs($s - $start);
    $string = '';
    foreach ($t as $key => &$val) {
        $$key = floor($s / $val);
        $s -= ($$key * $val);
        $string .= ($$key == 0) ? '' : $$key . "$key ";
    }
    if ($s != '0') {
        return $string . $s . 's';
    } else {
        return $string;
    }
}

function mtimetn()
{  //time stamp now
    //data oggi
    $dt = date("j");
    $mt = date("n");
    $yt = date("y");
    //ore oggi
    $timenow = getdate();
    $hn = substr("0" . $timenow["hours"], -2);
    $mih = substr("0" . $timenow["minutes"], -2);
    $sn = substr("0" . $timenow["seconds"], -2);
    $mtimet = mktime($hn, $mih, $sn, $mt, $dt, $yt);

    return $mtimet;
}

function maketimetn($nh, $nm, $ns)
{  //time stamp now
    //data oggi
    $dt = 0;
    $mt = 0;
    $yt = 0;
    //ore oggi
    //$timenow=getdate();
    //	$hn=substr("0" . $timenow["hours"], -2);
    //	$mih=substr("0" . $timenow["minutes"], -2);
    //	$sn=substr("0" . $timenow["seconds"], -2);

    $mtimet = mktime($nh, $nm, $ns, $mt, $dt, $y);

    return $mtimet;
}

function sectotime($sec)
{
    $s = $sec % 60;
    $m = $sec / 60;
    $h = $m / 60;
    $m = $m % 60;

    $r = (int) $h . "h " . (int) $m . "m " . (int) $s . "s ";
    return $r;
}

initbgvals();
