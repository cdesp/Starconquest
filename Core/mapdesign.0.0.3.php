<?php

include_once("myutils.php");
include_once("srvendturn.php");

global $stx, $sty, $planetstr, $planetstyl;
global $selplanet, $galaxysize;

function validmapclick($x, $y)
{
    global $mapoffsetx, $mapoffsety, $solsyssize, $tilesizex;

    $solsyswidth = $solsyssize * $tilesizex;
    $lx = $mapoffsetx;
    $rx = $lx + $solsyswidth * 3;
    $ty = $mapoffsety - 128;
    $by = $ty + $solsyswidth * 3;
    return $x > $lx and $x < $rx and $y > $ty and $y < $by;
}

function normalizemapxy(&$x, &$y, &$tx, &$ty)
{
    global $mapoffsetx, $mapoffsety, $solsyssize, $tilesizex;

    $ssx = $_SESSION['ssx'];
    $ssy = $_SESSION['ssy'];

    $f = validmapclick($x, $y);
    if ($f) {
        $x = $x - $mapoffsetx;
        $y = $y - $mapoffsety + 128;
        $tx = floor($x / $tilesizex);
        $ty = floor($y / $tilesizex);
        //	adddebugval('tx',$tx);
//    adddebugval('ty',$ty);

        $sox = max(0, $ssx - 1);
        $soy = max(0, $ssy - 1);
        //	adddebugval('offs x',$sox);
//    adddebugval('offs y',$soy);
        $tx += ($sox * $solsyssize);
        $ty += ($soy * $solsyssize);
    }

    return $f;
}

function normalizecoords(&$cx, &$cy)
{
    global $stx, $sty; //not global
    global $tilesizex, $tilesizey;
    global $solsyssize, $mapoffsety, $mapoffsetx;

    $x = $cx - $stx * $solsyssize; //quadr x
    $y = $cy - $sty * $solsyssize; //quadr y
    $rx = $mapoffsetx + $x * $tilesizex;
    $ry = $mapoffsety + $y * $tilesizey;

    $cx = $rx;
    $cy = $ry;
}

function docentertoplanet()
{
    $userid = $_SESSION['id'];

    if (myisset(filter_input(INPUT_GET, 'selplanet'))) {
        $selplanet = filter_input(INPUT_GET, 'selplanet');
        $_SESSION['selplanet'] = $selplanet;
        if (myisset(filter_input(INPUT_GET, 'action'))) {
            if (filter_input(INPUT_GET, 'action') == 'center') {
                centertoplanet($selplanet);
            }
        }
        adddebug("Planet selected $selplanet<br>");
    } elseif (!myisset(getsessionvar('selplanet'))) {
        adddebug("Setting default selected planet<br>");
        $selplanet = getusercapitol($userid);
        $_SESSION['selplanet'] = $selplanet;
        centertoplanet($selplanet);
        // adddebugval("ssx",$ssx);
        // 	 adddebugval("ssy",$ssy);
    }
}

function docentertofleet()
{
    $userid = $_SESSION['id'];

    if (myisset(getsessionvar('selfleet'))) {
        $selfleet = $_SESSION['selfleet'];
        centertofleet($selfleet);
        adddebug("fleet selected $selfleet<br>");
    }
}

/// New
function getmap($isajax = false)
{
    global $galaxysize, $planetstr, $planetstyl, $stx, $sty;

    adddebug("getmap<BR>");
    if (myisset(filter_input(INPUT_GET, 'action')) and filter_input(INPUT_GET, 'action') == 'center') {
        $_SESSION['selfleet'] = filter_input(INPUT_GET, 'selfleet');
        docentertofleet();
    } else {
        docentertoplanet();
    } //sets default planet


    $selplanet = $_SESSION['selplanet'];
    adddebugval("Galaxy size", $galaxysize);
    $ssx = $_SESSION['ssx'];
    $ssy = $_SESSION['ssy'];
    adddebugval("Solar system to center", "($ssx,$ssy)");
    adddebugval("Stx,y", "($stx,$sty)");



    if (myisset($selplanet)) {
        doendturn($selplanet);
    } else {
        doendturn();
    }

    //			$ajaxcode=" if (typeof initialized != 'undefined') {console.log('tt');return 0;}
    //		\n alert('me'); initialized=1;\n";


    $ajaxcode = " if (typeof initialized == 'undefined') initialized=1;\n";
    //		addplanets($ajaxcode);
//    	addfleetsonmap($ajaxcode);

    if (!$isajax) {
        addjscript($ajaxcode);
        addonloadfunction('getMapData();');
        $incjsf = getjsincludefile('jscript/mapdesign.js');
        addincludefile($incjsf);
    } else {
        $ajaxcode .= getajaxjsfunction('initform2', $ajaxcode);
    }
}

function addfleetsonmap(&$jscr)
{
    global $solsyssize, $tilesizex, $tilesizey, $mapoffsetx, $mapoffsety;
    global $bgquadrh, $bgquadrv, $ssx, $ssy;


    $fltarr = [];
    $i = 0;
    getvisiblemap($tx, $ty, $bx, $by);
    adddebugval('solsyssize', $solsyssize);
    $qres = getalluserfleets(null, $tx * $solsyssize, $ty * $solsyssize, ++$bx * $solsyssize, ++$by * $solsyssize);


    if (myisset($qres)) {
        $reccnt = query_num_rows($qres);
        adddebugval('reccnt', $reccnt);
        if ($reccnt > 0) { //we have visible fleets
            for ($i = 0; $i < $reccnt; $i++) {
                $dbarr = query_fetch_array($qres);
                $fltid = $dbarr['fltid'];
                $fltarr[$fltid] = $dbarr;
            }
        }

        if (myisset(filter_input(INPUT_GET, 'selfleet'))) {
            $selfleet = filter_input(INPUT_GET, 'selfleet');
            $_SESSION['selfleet'] = $selfleet;
        } elseif (myisset(getsessionvar('selfleet'))) {
            $selfleet = $_SESSION['selfleet'];
        } else {
            $selfleet = 0;
        }
    }

    if (!empty($fltarr) and $selfleet > 0 and array_key_exists($selfleet, $fltarr) and $fltarr[$selfleet] == null) {//add selected fleet also
        $qres = getfleetbyid($selfleet);
        $dbarr = query_fetch_array($qres);
        $fltarr[$i] = $dbarr;
    }

    $ssx = $_SESSION['ssx'];
    $ssy = $_SESSION['ssy'];
    $userid = $_SESSION['id'];
    adddebug("USERID=[$userid]<br>");
    $fleet_array = json_encode($fltarr);
    $jscr .= "
				 var fleetarr = " . $fleet_array . ";\n  var selfleet=$selfleet;\n
				 var solsyssize=$solsyssize;var topx=$tx;topy=$ty;botx=$bx;boty=$by; \n
				 var tilesizex=$tilesizex;var tilesizey=$tilesizey;
				 var mapoffsetx=$mapoffsetx;var mapoffsety=$mapoffsety;
				 var bgquadrh=$bgquadrh;var bgquadrv=$bgquadrv;
				 var ssx=$ssx;var ssy=$ssy;var userid=$userid;
				 ";
    adddebug("add fleets OK<br>");
}

function addplanets(&$jscr)
{
    global $solsyssize, $stx, $sty; //$maparr,$dbmaparr;

    getvisiblemap($stx, $sty, $sex, $sey);

    $qur = "SELECT * FROM `planets`  left join (select id,name as username from `users` ) as a  
		on a.id=`planets`.ownerid join `planettypes` on (`planets`.`typeid` = `planettypes`.`ptypeid`)
		WHERE (solsysx>=$stx) and (solsysy>=$sty) and (solsysx<=$sex) and (solsysy<=$sey) ";

    //$qres=query_exec($qur);
    //$qrows=query_num_rows($qres);
    executequery($qur, $qres, $qrcnt);


    //put in an array
    $maparr = array();
    $dbmaparr = array();
    for ($i = 0; $i < $qrcnt; $i++) {
        $dbarray = query_fetch_array($qres);
        $mx = $dbarray['coordx'];
        $my = $dbarray['coordy'];
        $code = $dbarray['pid'];
        $maparr[$mx][$my] = $code;
        $dbmaparr[$code] = $dbarray;
    }

    $selplanet = $_SESSION['selplanet'];

    $map_array = json_encode($maparr);
    $dbmap_array = json_encode($dbmaparr);
    $jscr .= "" .
            " var maparr = " . $map_array . ";\n  " .
            " var planetarr = " . $dbmap_array . ";\n".
            " var selplanet=$selplanet;\n
			   ";

    //adddebug($jscr)
    adddebug("Add planets OK<br>");
}

function addroutes(&$jscr)
{
    $route_array = '';
    $wayp_array = '';
    $wayparr = array();
    $routearr = array();
    if (myisset(getsessionvar('showroutes'))) {
        $qres = getalluserroutes();
        $qcnt = query_num_rows($qres);
        for ($i = 0; $i < $qcnt; $i++) {
            $trows = query_fetch_array($qres);
            $rtid = $trows['rtid'];
            $routearr[$i] = $trows;
            $wayparr[$rtid] = getwaypointsforroutejs($rtid);
        }
        $route_array = json_encode($routearr);
        $wayp_array = json_encode($wayparr);
    }

    if (myisset(getsessionvar('routesel'))) {
        $selroute = $_SESSION['routesel'];
    } else {
        $selroute = 0;
    }
    adddebugval('selroute', $selroute);
    $jscr .= "" .
            " var routearr = " . $route_array . ";\n  " .
            " var wayparr = " . $wayp_array . ";\n  " .
            " var selroute=$selroute;\n
			    ";
}

function getmapdata(&$ajaxcode)
{
    global $galaxysize;

    //get mapdata
    if (myisset(filter_input(INPUT_GET, 'ssx'))) {
        $ssx = filter_input(INPUT_GET, 'ssx');
        $ssy = filter_input(INPUT_GET, 'ssy');
        if ($ssx > $galaxysize - 2) {
            $ssx = $galaxysize - 2;
        }
        if ($ssy > $galaxysize - 2) {
            $ssy = $galaxysize - 2;
        }
        $_SESSION['ssx'] = $ssx;
        $_SESSION['ssy'] = $ssy;
        adddebugval('ssx', $ssx);
        adddebugval('ssy', $ssy);
    }

    addroutes($ajaxcode);
    addfleetsonmap($ajaxcode);
    addplanets($ajaxcode);
}
