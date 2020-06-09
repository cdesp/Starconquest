<?php

include_once "planetutils.php";
include_once "battleutils.php";
include_once "libastar.php";

function calculatespeed($power, $size)
{
    if ($size > 0) {
        return floor(($power / $size) * 100);
    } else {
        return 0;
    }
}

function getfleetowner($fltid)
{
    $qur = "select ownerid from `fleets` where `fltid`=$fltid LIMIT 1";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $trows = query_fetch_array($qres);
        return $trows['ownerid'];
    } else {
        return 0;
    }
}

function isfleetours($fltid)
{
    $qres = getfleetbyid($fltid);
    adddebug("Fleet id=$fltid<BR>");
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        $t = $trows['ownerid'];
        $t1 = $_SESSION['id'];
        adddebug("Fleet Owner ID=$t, User ID=$t1 <BR>");
        return $trows['ownerid'] == $_SESSION['id'];
    } else {
        return false;
    }
}

function getfleetbyid($fid)
{
    $qur = "select * from `fleets`,(select id,name as username from `users`) as a  where `fleets`.`ownerid`=a.`id` and `fltid`=$fid LIMIT 1";
    $qres = query_exec($qur);
    return $qres;
}

function getshipsoffleet($fltid)
{
    $qur = "select * from `fleetships`,`shiptypes` where fleetships.`stid`=shiptypes.`stid` and `fltid`=$fltid";
    $qres = query_exec($qur);
    //echo $qur;
    return $qres;
}

function getalluserfleets($usrid = null, $frmsx = null, $frmsy = null, $tosx = null, $tosy = null)
{
    //$userid=$_SESSION['id'];
    //if (!myisset($usrid)) $usrid=$userid;

    if (myisset($frmsy)) {
        $criter = " `coordx`>=$frmsx and `coordy`>=$frmsy and `coordx`<$tosx and `coordy`<$tosy ";
    } else {
        $criter = "";
    }
    if ($usrid != null) {
        $criter1 = " and `ownerid`=$usrid ";
    } else {
        $criter1 = "";
    }

    //	  if ($criter=="" and $criter1=="") $whr=""; else $whr=" where " ;
    if ($criter != "") {
        $criter = " and " . $criter;
    }

    $qur = "select * from `fleets`,(select id,name as username from `users`) as a  where `fleets`.`ownerid`=a.`id` $criter1 $criter order by `coordx`,`coordy`,`fltname`";
    //adddebugval('criter',$criter);
    //adddebugval('criter1',$criter1);
    //adddebug($qur.'<br>');
    $qres = query_exec($qur);
    return $qres;
}

function getfleetonroute($fltid)
{
    $qr = "select rtid from routes where `fltid`=$fltid LIMIT 1";
    $qres = query_exec($qr);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['rtid'];
    } else {
        return null;
    }
}

function getfleetofroute($rtid)
{
    $qr = "select fltid from routes where `rtid`=$rtid LIMIT 1";
    $qres = query_exec($qr);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['fltid'];
    } else {
        return null;
    }
}

function getalluserroutes($usrid = null, $frmsx = null, $frmsy = null, $tosx = null, $tosy = null)
{
    $userid = $_SESSION['id'];

    if (myisset($frmsy)) {
        $criter = " and `curcoordx`>$frmsx and `curcoordy`>frmsy and `curcoordx`<$tosx and `curcoordy`<$tosy ";
        $order = "`curcoordx`,`curcoordy`";
    } else {
        $criter = "";
        $order = "`eta` ";
    }
    if (!myisset($usrid)) {
        $usrid = $userid;
    }

    $qur = "select * from `routes` where `ownerid`=$usrid $criter order by $order";
    $qres = query_exec($qur);
    return $qres;
}

function getwaypointsforroute($rtid)
{
    global $dbg;

    $retarr = array();
    $qr = "select * from `routes` where `rtid`=$rtid LIMIT 1";
    if (executequery($qr, $qres, $qcnt)) {
        $trows = query_fetch_array($qres);
        $retarr[0]['wx'] = $trows['curcoordx'];
        $retarr[0]['wy'] = $trows['curcoordy'];
        $retarr[0]['eta'] = 0;
    }
    $qr = "select * from `routeways` where `rtid`=$rtid order by `wid`";
    if (executequery($qr, $qres, $qcnt)) {
        for ($i = 1; $i < $qcnt + 1; $i++) {
            $trows = query_fetch_array($qres);
            $retarr[$i]['wx'] = $trows['wx'];
            $retarr[$i]['wy'] = $trows['wy'];
            $retarr[$i]['eta'] = $trows['eta'];
        }
    }


    return $retarr;
}

function getwaypointsforroutejs($rtid)
{
    global $dbg;

    $retarr = array();
    $qr = "select * from `routes` where `rtid`=$rtid LIMIT 1";
    if (executequery($qr, $qres, $qcnt)) {
        $trows = query_fetch_array($qres);
        $retarr[0]['fltid'] = $trows['fltid'];
        $retarr[0]['wx'] = $trows['curcoordx'];
        $retarr[0]['wy'] = $trows['curcoordy'];
        $retarr[0]['eta'] = 0;
    }
    $qr = "select * from `routeways` where `rtid`=$rtid order by `wid`";
    if (executequery($qr, $qres, $qcnt)) {
        for ($i = 1; $i < $qcnt + 1; $i++) {
            $trows = query_fetch_array($qres);
            $retarr[$i]['wx'] = $trows['wx'];
            $retarr[$i]['wy'] = $trows['wy'];
            $retarr[$i]['eta'] = $trows['eta'];
        }
    }


    return $retarr;
}

function deletefleet($fltid)
{
    $qr = "delete from `fleets` where `fltid`=$fltid";
    query_exec($qr);
    $qr = "delete from `fleetships` where `fltid`=$fltid";
    query_exec($qr);
    // adddebug(" fleet $fltid DELETED <br>");
}

function putfleetonplanet($fltid, $pid)
{
    // adddebug(" put ships of fleet $fltid on planet $pid and destroy fleet <br>");
    $qres = getshipsoffleet($fltid);
    $qcnt = query_num_rows($qres);
    if ($qcnt > 0) {
        //adddebug("FLEET SHIPS: $qcnt");
        for ($i = 0; $i < $qcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $stid = $dbarr['stid'];
            $quant = $dbarr['quantity'];
            adddebugval("$quant ships added of type  $stid to planet $pid");
            addshipstoplanet($pid, $quant, $stid);
        }
        // adddebug("FLEET $fltid DELETED!");
        deletefleet($fltid);
    } else {
        adddebug("ERROR SHOULD NOT COME HERE! FLEET PUT ON PLANET<BR>");
    }
}

function addshipstoplanet($pid, $quant, $stid)
{
    $userid = $_SESSION['id'];

    //adddebug('addshipstoplanet<br>');
    $sid = null;
    $quer = "select * from `ships` where `pid`=$pid and `stid`=$stid LIMIT 1";
    if (executequery($quer, $qres, $qrcnt)) {
        $dbarr = query_fetch_array($qres);
        $oldquant = $dbarr['quantity'];
        $sid = $dbarr['sid'];
    } else {
        $oldquant = 0;
    }

    // adddebugval('oldquant',$oldquant);

    $newquant = $oldquant + $quant;
    // adddebugval('newquant',$newquant);
    // adddebugval('sid',$sid);
    if ($sid != null) {
        $quer = "update `ships` set `quantity`=$newquant where sid=$sid";
    } else {
        $sid = gettablenextid('ships', 'sid');
        $quer = "insert into `ships` (`sid`,`stid`,`quantity`,`pid`,`fltid`,`owner`) 
					values($sid,$stid,$newquant,$pid,0,$userid)";
    }
    $plname = getplanetname($pid);
    newsysmessage("$quant ships added of type $stid to planet $plname", "Ships");
    // addbottomoutput( $quer);
    query_exec($quer);
}

function addshipinfo(&$retarr, $upgarr, $ishull = false)
{
    if ($ishull) {
        $retarr['maxsize'] = $upgarr['size'];
    } else {
        $retarr['size'] += $upgarr['size'];
    }
    $retarr['ngold'] += $upgarr['ngold'];
    $retarr['nmetalum'] += $upgarr['nmetalum'];
    $retarr['ntritium'] += $upgarr['ntritium'];
}

function getshiptypename($stid)
{
    $result = getfieldfromdbtable('shiptypes', 'stypename', 'stid', $stid);
    adddebug("SHIPTYPE NAME:$result<BR>");
    if (!$result) {
        return "Unknown Ship Type";
    } else {
        return $result;
    }
}

function getshipinfo($dbarr)
{
    $retarr = $dbarr;
    //	  $retarr['stid']=$dbarr['stid'];
    //	  $retarr['stypename']=$dbarr['stypename'];

    $retarr['maxsize'] = 0;
    $retarr['size'] = 0;
    $retarr['ngold'] = 0;
    $retarr['nmetalum'] = 0;
    $retarr['ntritium'] = 0;

    $id = $dbarr['propulsionid'];
    $qr = "select * from x_propulsions where `xpropid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['engpower'] = $upgarr['power'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['computerid'];
    $qr = "select * from `x_computers` where `xcompid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['accuracy'] = $upgarr['accuracy'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['hullid'];
    $qr = "select * from `x_hulls` where `xhullid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['armor'] = $upgarr['armor'];
        $retarr['image'] = $upgarr['image'];
        addshipinfo($retarr, $upgarr, true);
    }
    $id = $dbarr['sensorid'];
    $qr = "select * from `x_sensors` where `xsensid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['sensdist'] = $upgarr['distance'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['shieldid'];
    $qr = "select * from `x_shields` where `xshieldid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['shldpower'] = $upgarr['power'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['weapon1id'];
    $qr = "select * from x_weapons where `xweaponid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['wname1'] = $upgarr['weaponname'];
        $retarr['wdist1'] = $upgarr['weapondist'];
        $retarr['wdmg1'] = $upgarr['weapondmg'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['weapon2id'];
    $qr = "select * from x_weapons where `xweaponid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['wname2'] = $upgarr['weaponname'];
        $retarr['wdist2'] = $upgarr['weapondist'];
        $retarr['wdmg2'] = $upgarr['weapondmg'];
        addshipinfo($retarr, $upgarr);
    }
    $id = $dbarr['weapon3id'];
    $qr = "select * from x_weapons where `xweaponid`=$id";
    if (executequery($qr, $qres, $qrcnt)) {
        $upgarr = query_fetch_array($qres);
        $retarr['wname3'] = $upgarr['weaponname'];
        $retarr['wdist3'] = $upgarr['weapondist'];
        $retarr['wdmg3'] = $upgarr['weapondmg'];
        addshipinfo($retarr, $upgarr);
    }


    $speed = calculatespeed($retarr['engpower'], $retarr['maxsize']);
    $retarr['speed'] = $speed;

    return $retarr;
}

//gets the shiptypo info
function getallshipinfo($stid)
{
    $qr = "select * from shiptypes where `stid`=$stid limit 1";
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $qrarr = query_fetch_array($qrres);
        $qrarr = getshipinfo($qrarr);
        return $qrarr;
    } else {
        return false;
    }
}

function calculatemaxships($plg, $plm, $plt, $g, $m, $t)
{
    $g1 = floor($plg / $g);
    $m1 = floor($plm / $m);
    $t1 = floor($plt / $t);

    return min($g1, $m1, $t1);
}

function gettimetoreach($waypoints, $speed)
{
    return -1;
}

function getspeedoffleet($fltid, $fltshipsres = null)
{
    if (!myisset($fltshipsres)) {
        $fltshipsres = getshipsoffleet($fltid);
    }
    $fscnt = query_num_rows($fltshipsres);
    if ($fscnt > 0) {
        $speed = 9999;
        for ($i = 0; $i < $fscnt; $i++) {
            $fsarr = query_fetch_array($fltshipsres);
            $fsarr = getshipinfo($fsarr);
            $speed = min($speed, $fsarr['speed']);
        }
    } else {
        $speed = -1;
    }

    return $speed;
}

function addroute($fltid, $tx, $ty)
{
    $userid = $_SESSION['id'];
    $ret = '';

    $qres = getfleetbyid($fltid);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        $curcx = $trows['coordx'];
        $curcy = $trows['coordy'];
        $sfres = getshipsoffleet($fltid);
        $speed = getspeedoffleet($fltid, $sfres);
        $rtid = getfleetonroute($fltid);
        if (!myisset($rtid)) {
            $rtid = gettablenextid('routes', 'rtid');
            //    $waypoints=get_line($curcx,$curcy,$tx,$ty);
            $waypoints = get_starline($curcx, $curcy, $tx, $ty);

            //		     $eta=gettimetoreach($waypoints,$speed);
            $eta = addroutewaypoints($rtid, $speed, $waypoints);
            $ret .= 'ETA ' . date("d-m-Y H:i:s", $eta);

            $qr = "insert into `routes` (rtid,fltid,tocoordx,tocoordy,curcoordx,curcoordy,fspeed,eta,ownerid) 
		                     values ($rtid,$fltid,$tx,$ty,$curcx,$curcy,$speed,$eta,$userid)";
            query_exec($qr);
        } else {
            $ret .= ("ALREADY ON ROUTE!!!. \n Maybe we can have a tech to allow stop and reroute!!");
        }
        $_SESSION['route'] = null; //end routing
    }

    return $ret;
}

function addroutewaypoints($rtid, $speed, $arr)
{
    $starttime = mtimetn();
    $soldur = getinsolarduration($speed);
    $arrcnt = count($arr);
    $prex = $arr[0]['x'];
    $prey = $arr[0]['y'];
    for ($i = 1; $i < $arrcnt; $i++) {
        $x = $arr[$i]['x'];
        $y = $arr[$i]['y'];
        if (solsyschange($prex, $prey, $x, $y)) {
            $dur = getoutsolarduration($speed);
        } else {
            $dur = getinsolarduration($speed);
        } //in minutes
            
        //$duration= adddst(makeshiptimetoupg(0,0,$dur,0));
        $duration = makeshiptimetoupg(0, 0, $dur, 0);
        adddebug("---------------------");
        adddebugval('duration', $duration);
        //debug 20 secs for each klik
        $duration = 20;
        $eta = $starttime + $duration;
        adddebugval('ETA', date("d-m-Y H:i:s", $eta));

        //$durs=getshiptime(deldst($duration));
        $durs = getshiptime($duration);
        //	$etas=getshiptime(deldst(calctimetoupgrade($starttime,$duration)));

        adddebug("$i. dur=$durs <br> ");

        adddebugval("$i. x,y", $x . ':' . $y);
        $qr = "insert into `routeways` (rtid,wid,wx,wy,sttime,duration,eta) values ($rtid,$i,$x,$y,$starttime,$duration,$eta)";
        query_exec($qr);

        $starttime = $eta; //new starttime; for next
        $prex = $x;
        $prey = $y;
    }
    return $eta;
}

function getinsolarduration($speed)
{
    $quadrantdistance = 1000; //1000 clicks is the distance from one quadr to another
    //speed is the clicks a fleet can travel in a minute say 50 so
    //1000 clicks will be traveled in 1000/50 minutes is 20 minutes
    //or if speed is the clicks in an hour and the distance is  10 clicks
    // then it is 50 clicks in 60 mins so 500/60 in mins 8,3 mins
    $timetochangequadrant = $quadrantdistance / $speed; //in minutes
    return $timetochangequadrant;
}

function getoutsolarduration($speed)
{
    $quadrantdistance = 30000; //100000 clicks is the distance from one solar sys  to another
    //speed is the clicks a fleet can travel in a minute say 50 so
    //100000 clicks will be traveled in 300000/50 minutes is 600 minutes = 10 hours
    //or if speed is the clicks in an hour and the distance is  10 clicks
    // then it is 50 clicks in 60 mins so 500/60 in mins 8,3 mins
    $timetochangequadrant = $quadrantdistance / $speed; //in minutes
    return $timetochangequadrant;
}

function solsyschange($fx, $fy, $tx, $ty)
{
    global $solsyssize;
    $fsx = floor($fx / $solsyssize);
    $fsy = floor($fy / $solsyssize);
    $tsx = floor($tx / $solsyssize);
    $tsy = floor($ty / $solsyssize);

    if (($fsx <> $tsx) or ($fsy <> $tsy)) {
        return true;
    } else {
        return false;
    }
}

function getenemyfleetsfromcoords($cx, $cy)
{
    $userid = $_SESSION['id'];

    $qur = "select * from `fleets` where `ownerid`<>$userid and `coordx`=$cx and `coordy`=$cy order by fltid";
    $qres = query_exec($qur);
    return $qres;
}

function checkfleetatdest($cx, $cy, $fltid)
{
    //adddebug("Check fleet $fltid at [$cx,$cy]<BR>");
    $qres = getenemyfleetsfromcoords($cx, $cy);

    if (query_num_rows($qres) > 0) {
        //we have a battle
        adddebug("Battle for fleet $fltid at [$cx,$cy] <BR>");
        dofleetbattle($fltid, $qres);
    }
}

function checkplanetatdest($cx, $cy, $fltid)
{
    $userid = $_SESSION['id'];

    adddebug("checkplanetatdest $cx,$cy for fleet:$fltid<BR>");
    $ret = false;
    adddebugval('cx', $cx);
    adddebugval('cy', $cy);
    $plres = getplanetatcoords($cx, $cy);
    if ($plres != null) {
        $plarr = query_fetch_array($plres);
        $pid = $plarr['pid'];
        adddebug("Planet $pid Found at destination<br>");
        doendturn($pid); //get the planet up to date
        adddebugval('pid', $pid);
        getplanetowner($pid, $uid, $uname);
        adddebugval('uid', $uid);
        adddebug("Userid=$userid<BR>");

        if ($uid != $userid) {
            //attack planet
            adddebug("Attack on planet $pid<BR>");
            if ($uid == 0) { //just take it
                changeplanetowner($pid, $userid);
                putfleetonplanet($fltid, $pid);
                $ret = true;
            } else {
                doplanetbattle($fltid, $pid);
                $ret = true;
            }
        } else { //its one of ours just put the fleet on the planet
            adddebug("Put fleet $fltid on our planet $pid<BR>");
            putfleetonplanet($fltid, $pid);
            $ret = true;
        }
    } else {
        adddebug("No Planet at coords $cx,$cy<BR>");
        if (array_key_exists('isajax', $_SESSION) and $_SESSION['isajax']) {
            adddebug("with AJAX <BR>");
        } else {
            adddebug("No AJAX <BR>");
        }
    }

    return $ret;
}

function isattacker($batid, $fltid)
{
    $fltuser = getfleetowner($fltid);
    //check attacker
    $fc = 'Y';
    $k = 1;
    do {
        $qrres = getbattlefleets($batid, 0, $fc, $qrcnt, true);
        adddebugval('battlefleets', $qrcnt);
        for ($i = 0; $i < $qrcnt; $i++) {
            $qrarr = query_fetch_array($qrres);
            $ownerid = $qrarr['ownerid'];
            $attacker = $qrarr['attacker'];
            adddebugval('ownerid', $ownerid);
            adddebugval('fltuser', $fltuser);
            if ($ownerid == $fltuser) {//here we can check for allies of ownerid
                return $attacker = 'Y';
            }
        }
        $fc = 'N'; //check defender
    } while ($k++ < 2);

    return null;
}

//check if fleet already in battle return true if not in battle false if it is on battle
function validatenewfleet($batid, $fltid)
{
    $qr = "select * from `battlefleets` where `batid`=$batid and `fltid`=$fltid";
    // return !(executequery($qr, $qres, $qrcnt) && $qrcnt>0); //if we find this fleet then it is already in battle
    if (executequery($qr, $qres, $qrcnt)) {
        adddebug("Fleet $fltid is in battle $batid? result: $qrcnt <BR>");
        if ($qrcnt == 0) {
            return true;
        }
    }
    return false;
}

function checkbattleatdest($cx, $cy, $fltid)
{
    $batid = getbattleatcoords($cx, $cy);
    if ($batid != false) {
        if (!validatenewfleet($batid, $fltid)) {
            adddebug("Fleet $fltid is already in battle<BR>");
            return true;
        } else {
            adddebug("Fleet $fltid is NOT in battle<BR>");
        }
        $isatck = isattacker($batid, $fltid);
        $round = getbattlenextround($batid);
        adddebugval('attck', $isatck);
        adddebugval('round', $round);
        //adddebug('inserting fleet to battle<br>');
        if ($isatck != null) {
            insertbattlefleet($batid, $fltid, $isatck, $round);
        } else {
            adddebug('not an attacking or defending fleet<br>');
        }

        return true;
    } else {
        return false;
    }
}

function fleetdestreached($fltid)
{
    adddebug("Fleet $fltid reached destination<BR>");

    $qres = getfleetbyid($fltid);
    $dbarr = query_fetch_array($qres);
    $cx = $dbarr['coordx'];
    $cy = $dbarr['coordy'];
    $fltname = $dbarr['fltname'];
    newsysmessage("Fleet $fltname reached destination [$cx:$cy] ", "Fleet");

    $r = checkbattleatdest($cx, $cy, $fltid);
    adddebug("Return value was $r (1 is true) <BR>");

    if (!$r) {
        if (!checkplanetatdest($cx, $cy, $fltid)) {//else we already battle for planet
            adddebug("Check Fleet $fltid at Dest [$cx,$cy]<BR>");
            checkfleetatdest($cx, $cy, $fltid);
        }
    }
}

function setnewcoordsforroute($rtid, $newx, $newy, $wid = null)
{
    $qr = "update routes set curcoordx=$newx,curcoordy=$newy where `rtid`=$rtid";
    query_exec($qr);
    $fltid = getfleetofroute($rtid);
    if (myisset($fltid)) {
        $qr = "update fleets set coordx=$newx,coordy=$newy where `fltid`=$fltid";
        query_exec($qr);
    }

    if (myisset($wid)) {
        $qr = "delete from routeways where `rtid`=$rtid and `wid`=$wid";
        query_exec($qr);
        $qr = "select * from routeways where `rtid`=$rtid LIMIT 1";
        if (executequery($qr, $qres, $qrcnt)) {
            if ($qrcnt == 0) { //no more waypoints we reached destination
                $qr = "delete from routes where `rtid`=$rtid ";
                query_exec($qr);
                adddebug("$fltid reached Destination.");
                fleetdestreached($fltid);
            }
        }
    }
}

function getshipsonplanet($pid, $uid, &$qrcnt)
{
    checkshipbuild3($pid);
    $quer = "
			SELECT * FROM `ships`,`shiptypes`,`x_hulls`  where ownerid=$uid and `hullid`=`xhullid` and `pid`=$pid and ships.stid=shiptypes.stid and ships.quantity>0 order by size;
		";
    //adddebug($quer."<br>");
    if (executequery($quer, $qres, $qrcnt)) {
        return $qres;
    } else {
        return false;
    }
}

function calculatebuildtimeforship($stid)
{
    //todo:calc this
    return makeshiptimetoupg(0, 0, 1, 0); //1 min
}

function getfleetsolarsystem($fltid, &$psx, &$psy)
{
    global $solsyssize;

    $qur = "select coordx,coordy from `fleets` where `fltid`=$fltid";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        $psx = floor($trows['coordx'] / $solsyssize);
        $psy = floor($trows['coordy'] / $solsyssize);
    } else {
        adddebug("error getting fleet solsys: fltid=$fltid");
    }
}

function centertofleet($fltid)
{
    global $ssx, $ssy, $galaxysize;
    getfleetsolarsystem($fltid, $ssx, $ssy);
    if ($ssx > $galaxysize - 2) {
        $ssx = $galaxysize - 2;
    }
    if ($ssx < 1) {
        $ssx = 1;
    }
    if ($ssy > $galaxysize - 2) {
        $ssy = $galaxysize - 2;
    }
    if ($ssy < 1) {
        $ssy = 1;
    }
    $_SESSION['ssx'] = $ssx;
    $_SESSION['ssy'] = $ssy;
    adddebug("centered to fleet:$fltid($ssx:$ssy)<br>");
}
