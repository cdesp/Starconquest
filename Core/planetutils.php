<?php

include_once "economy.php";
global $plg, $plm, $plt;

function getbuildingsforplanet($pid, &$qrcnt)
{


//		$quer = "SELECT * FROM `buildings`,`buildingtypes`,`buildinglevels` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=`buildinglevels`.btid and ((`level`=`blevel`+1) or (`level`=`bmaxlevel`-1))  order by btype";

    $quer = "SELECT * FROM `buildings`,`buildingtypes`,`buildinglevels` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=`buildinglevels`.btid and 
level in (select min(level) from `buildinglevels` where
((`level`=`blevel`+1) or (`level`=`bmaxlevel`-1 ) )  )";

    if (executequery($quer, $qres, $qrcnt)) {
        return $qres;
    } else {
        $qrcnt = 0;
        return false;
    }
}

function addbuildingstoplanet($pid)
{
    $quer = "SELECT * FROM buildingtypes";
    //for each type add a building for planet if it doesnot exists
    adddebug("Adding buildings to planet $pid<br>");
    if (executequery($quer, $qres, $qrcnt)) {
        //adddebug("Found $qrcnt Building types<br>");
        //			 query_exec("SET AUTOCOMMIT=0");
        //			 query_exec("START TRANSACTION");

        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            if (!$dbarr) {
                break;
            }
            $btid = $dbarr['btid'];
            $quer = "SELECT * FROM buildings WHERE `pid`=$pid and `btid`=$btid";
            executequery($quer, $bldres, $bldcnt);
            //adddebugval('buildings',$bldcnt);
            if ($bldcnt == 0) {
                //add a new building
                adddebug("adding new building : $btid <br>");
                $flds = 'pid,btid';
                $vls = "$pid,$btid";
                $quer = "INSERT INTO `buildings` ($flds) values ($vls)";
                $insres = query_exec($quer);
                if (!$insres) {
                    adddebug("Error adding new building : $quer <br>");
                }
            }
            //else
            //  adddebug(" building : $btid exists<br>");
        }
        //			 query_exec("COMMIT");
    }
}

function addnewprodforplanet($pid, $par = null)
{
    //todo:check that total percentage is 100%
    $transactarr = array();


    if (!myisset($par)) {
        $parst = "`bparentid` is NULL";
        $pr = 0;
    } else {
        $parst = "`bparentid`=$par";
        $pr = $par;
    }

    $quer = "SELECT * FROM `buildings`,`buildingtypes` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and $parst order by btype";

    if (executequery($quer, $qres, $qrcnt)) {
        //adddebugval('qrcnt',$qrcnt);
        $totalperc = 0;
        for ($i = 1; $i < $qrcnt + 1; $i++) {
            $transactarr[$i] = '';
            $dbarr = query_fetch_array($qres);
            $bperc = $dbarr['percnt'];
            $btid = $dbarr['btid'];
            if (!myisset($par)) { //one level down only
                addnewprodforplanet($pid, $btid);
            }


            $compname = "buildrng_$pr$i";
            $inpcomp=filter_input(INPUT_POST, "$compname");
            if (myisset($inpcomp)) {
                $newval = $inpcomp;

                $totalperc = $totalperc + $newval;
                if ($bperc != $newval) {
                    // adddebug("$qrcnt updating<br>");
                    $qr = "update `buildings` set `percnt`=$newval where `pid`=$pid  and `btid`=$btid";
                    $transactarr[$i] = $qr;
                    // query_exec($qr);
                }
            } else {
                $totalperc = $totalperc + $bperc;
            }
        }

        if (($totalperc > 100) and ($qrcnt > 0)) {
            adddebug("$qrcnt Data rolled vback<br>");
            $t = $_SERVER['QUERY_STRING'];
            header("Location: index.php?$t&msg='Total percentage " . $totalperc . "% is not 100% try again' ");
        } else {
            for ($i = 1; $i < $qrcnt + 1; $i++) {
                if ($transactarr != '') {
                    if ($transactarr[$i] != '') {
                        query_exec($transactarr[$i]);
                    }
                }
            }
            adddebug("$qrcnt Data comitted<br>");
        }
    } else {
        addbottomoutput($quer);
    }
}

function upgradeplanetbuilding($pid, $btid)
{
    $qur = "update `buildings` set `blevel`=`blevel`+1, `baction`=0,  `bacttimestart`=0, `bacttimedur`=0, `bactfinished`=0 where pid=$pid and btid=$btid";
    $qres = query_exec($qur);
    //echo $qur;
}

//start upgrade from planet resources
function upgradebuilding($pid, $compid, $par = null)
{
    //todo:check that total percentage is 100%
    $gb = true;
    $mb = true;
    $tb = true;
    $transactarr = array();
    $f = true;

    if (!myisset($par)) {
        $parst = "`bparentid` is NULL";
        $pr = 0;
    } else {
        $parst = "`bparentid`=$par";
        $pr = $par;
    }

    $quer = "SELECT * FROM `buildings`,`buildingtypes`,`buildinglevels` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=`buildinglevels`.btid and (`level`=`blevel`+1 and `blevel'<`bmaxlevel`-1) and $parst order by btype";

    if (executequery($quer, $qres, $qrcnt)) {
        //adddebugval('qrcnt',$qrcnt);
        for ($i = 1; $i < $qrcnt + 1; $i++) {
            $dbarr = query_fetch_array($qres);
            $btid = $dbarr['btid'];
            if (!myisset($par)) { //one level down only
                upgradebuilding($pid, $compid, $btid);
            }


            $compname = "buildrng_$pr$i";
            $comptoupg = "buildrng_" . $compid;
            adddebugval('compname', $compname);
            adddebugval('compname', $comptoupg);
            if ($compname == $comptoupg) {
                //check to see if resources exist
                //then upgrade building
                $quer = "SELECT * FROM `planets` WHERE `pid`=$pid";
                if (executequery($quer, $plqres, $plqrcnt)) {
                    $pldbarr = query_fetch_array($plqres);

                    $bgupg = $dbarr['goldupg'];
                    $bmupg = $dbarr['metalumupg'];
                    $btupg = $dbarr['tritiumupg'];
                    $bupglevel = $dbarr['blevel'] + 1;

                    //	 getupgresneeded($bgupg,$bmupg,$btupg,$bupglevel);

                    $gb = $pldbarr['gold'] >= $bgupg;
                    $mb = $pldbarr['metalum'] >= $bmupg;
                    $tb = $pldbarr['tritium'] >= $btupg;


                    if ($gb and $mb and $tb) { //resources are available
                        adddebug('resources available<br>');
                        $newg = $pldbarr['gold'] - $bgupg;
                        $newm = $pldbarr['metalum'] - $bmupg;
                        $newt = $pldbarr['tritium'] - $btupg;
                        $qr = "update `planets` set `gold`=$newg,`metalum`=$newm,`tritium`=$newt where `pid`=$pid ";
                        query_exec($qr);
                        //	 echo $qr.'<br>';
                        //start upgrading
                        $tmst = mtimetn();
                        $upgdays = $dbarr['daysupg'];
                        $upghours = $dbarr['hoursupg'];
                        $upgmins = $dbarr['minsupg'];
                        $upglevel = $dbarr['blevel'] + 1;
                        $tmdur = maketimetoupg($upgdays, $upghours, $upgmins);
                        $tmfinished = $tmst + $tmdur;


                        adddebugval('st', date("d : H:i:s", $tmst));
                        adddebugval('dur', gettimetoupgrade($tmdur));
                        adddebugval('fin', date("d : H:i:s", $tmfinished));
                        $qr = "update `buildings` set `baction`=1, `bacttimestart`=$tmst,`bacttimedur`=$tmdur, `bactfinished`=$tmfinished where `pid`=$pid and `btid`=$btid";
                        //	 echo $qr.'<br>';

                        query_exec($qr);

                        break;
                    }
                }//else planet not found????
            }
        }

        if (!($gb and $mb and $tb)) {
            // adddebug("$qrcnt Data rolled vback<br>");
            $t = $_SERVER['QUERY_STRING'];
            header("Location: index.php?$t&msg='Not enough resources or max upgrade reached!!!' ");
            //echo"Location: index.php?$t&msg='Not enough resources!!!' ";
        }
    } else {
        addbottomoutput($quer);
    }

    return $gb and $mb and $tb;
}

//start upgrade from planet resources
function upgradebuilding2($pid, $btid)
{
    $gb = true;
    $mb = true;
    $tb = true;
    $transactarr = array();
    $f = true;


    $quer = "SELECT * FROM `buildings`,`buildingtypes`,`buildinglevels` WHERE `pid`=$pid and `buildings`.`btid`=$btid and `buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=`buildinglevels`.btid and (`level`=`blevel`+1) and (`blevel`<`bmaxlevel`-1) order by btype";
    if (executequery($quer, $qres, $qrcnt) and $qrcnt > 0) {
        //			adddebugval('qrcnt',$qrcnt);
        $dbarr = query_fetch_array($qres);
        if ($dbarr['baction'] == 0) {
            //check to see if resources exist
            //then upgrade building
            $quer = "SELECT * FROM `planets` WHERE `pid`=$pid";
            if (executequery($quer, $plqres, $plqrcnt)) {
                $pldbarr = query_fetch_array($plqres);

                $bgupg = $dbarr['goldupg'];
                $bmupg = $dbarr['metalumupg'];
                $btupg = $dbarr['tritiumupg'];
                $bupglevel = $dbarr['blevel'] + 1;

                //	 getupgresneeded($bgupg,$bmupg,$btupg,$bupglevel);

                $gb = $pldbarr['gold'] >= $bgupg;
                $mb = $pldbarr['metalum'] >= $bmupg;
                $tb = $pldbarr['tritium'] >= $btupg;


                if ($gb and $mb and $tb) { //resources are available
                    adddebug('resources available<br>');
                    $newg = $pldbarr['gold'] - $bgupg;
                    $newm = $pldbarr['metalum'] - $bmupg;
                    $newt = $pldbarr['tritium'] - $btupg;
                    $qr = "update `planets` set `gold`=$newg,`metalum`=$newm,`tritium`=$newt where `pid`=$pid ";
                    query_exec($qr);
                    //	 echo $qr.'<br>';
                    //start upgrading
                    $tmst = mtimetn();
                    $upgdays = $dbarr['daysupg'];
                    $upghours = $dbarr['hoursupg'];
                    $upgmins = $dbarr['minsupg'];
                    $upglevel = $dbarr['blevel'] + 1;
                    $tmdur = maketimetoupg($upgdays, $upghours, $upgmins);
                    $tmfinished = $tmst + $tmdur;


                    adddebugval('st', date("d : H:i:s", $tmst));
                    adddebugval('dur', gettimetoupgrade($tmdur));
                    adddebugval('fin', date("d : H:i:s", $tmfinished));
                    $qr = "update `buildings` set `baction`=1, `bacttimestart`=$tmst,`bacttimedur`=$tmdur, `bactfinished`=$tmfinished where `pid`=$pid and `btid`=$btid";
                    //	 echo $qr.'<br>';

                    query_exec($qr);
                    return 'Building is upgrading.';
                } else {
                    return 'Not enough resources';
                }
            }//else planet not found????
        }//baction=0 end
        else {
            return 'Already upgrading!!!';
        }
    } else {
        addbottomoutput($quer);
        return 'Max level reached!!! ';
    }

    //		return $gb and $mb and $tb;
}

function getplanethourprod($pid, &$g, &$m, &$t)
{
    $g = calcplanetproduction($pid, 'gold', $g);
    $m = calcplanetproduction($pid, 'metalum', $m);
    $t = calcplanetproduction($pid, 'tritium', $t);
}

function getcoordsfromplanet($pid, &$cx, &$cy)
{
    $qur = "Select `coordx`,`coordy` from planets where `pid`=$pid LIMIT 1";
    if (executequery($qur, $qres, $qcnt)) {
        $trows = query_fetch_array($qres);
        $cx = $trows['coordx'];
        $cy = $trows['coordy'];
        return true;
    } else {
        return false;
    }
}

function getplanetfromcoords($cx, $cy)
{
    $qur = "Select pid from planets where `coordx`=$cx and `coordy`=$cy";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['pid'];
    } else {
        return null;
    }
}

//gets 1st planet of solsystem
function getplanetfromsolcoords($sx, $sy)
{
    $qur = "Select pid from planets where `solsysx`=$sx and `solsysy`=$sy";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['pid'];
    } else {
        return null;
    }
}

function getsolsysplanets($solx, $soly, &$qrcnt)
{
    $qur = 'SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`)';
    $qur .= " and (solsysx=$solx) and (solsysy=$soly)";
    if (executequery($qur, $qres, $qrcnt)) {
        return $qres;
    } else {
        return null;
    }
}

function getnewplanetname()
{
    return 'plan' . rand(10, 100) . rand(10, 100);
}

function getplanetname($pid)
{
    $qur = "select `name` from `planets` where `pid`=$pid";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['name'];
    } else {
        adddebug("error getting planet name: pid=$pid");
        return "error";
    }
}

function getplanetinfo($pid)
{
    $qur = "SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`) and `pid`=$pid";

    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        return query_fetch_array($qres);
    } else {
        adddebug("error getting planet info: pid=$pid");
        return false;
    }
}

function getplanetsolarsystem($pid, &$psx, &$psy)
{
    $qur = "select solsysx,solsysy from `planets` where `pid`=$pid";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        $psx = $trows['solsysx'];
        $psy = $trows['solsysy'];
    } else {
        adddebug("error getting planet solsys: pid=$pid");
    }
}

function getplanetowner($pid, &$uid, &$uname)
{
    $qur = "select ownerid from `planets` where `pid`=$pid";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        //adddebugval("PLID",$pid);
        $uid = $trows['ownerid'];
        $trows = getuserdatabyid($uid);
        $uname = $trows['name'];
        // adddebugval("Uid",$uid);
    } else {
        adddebug("error getting planet owner: pid=$pid");
    }
}

function getplanets($stx, $sty, $sex, $sey, &$qrcnt)
{
    $qur = 'SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`)';
    $qur = $qur . " and (solsysx>=$stx) and (solsysy>=$sty) and (solsysx<=$sex) and (solsysy<=$sey)";
    //echo $qur;
    $qres = query_exec($qur);
    if (executequery($qur, $qres, $qrcnt)) {
        return $qres;
    } else {
        return false;
    }
}

;

function getalluserplanets($usrid = null)
{
    if (!myisset($usrid) and!myisset($_SESSION['id'])) {
        $qur = "select * from `planets`,`planettypes` where `typeid`=`ptypeid`";  //get all planets
    } else {
        if (!myisset($usrid)) {
            $usrid = $_SESSION['id'];
        }

        $qur = "select * from `planets`,`planettypes` where `ownerid`=$usrid and `typeid`=`ptypeid`";
    }

    $qres = query_exec($qur);
    return $qres;
}

function getplanetcount($usrid = null)
{
    $userid = $_SESSION['id'];

    if (!myisset($usrid)) {
        $usrid = $userid;
    }
    $qur = "select count(*) from `planets` where `ownerid`=$usrid ";
    if (executequery($qur, $qres, $qcnt)) {
        return $qcnt;
    }
}

function centertoplanet($pid)
{
    global $ssx, $ssy, $galaxysize;

    getplanetsolarsystem($pid, $ssx, $ssy);

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
    adddebug("centered to planet:$pid($ssx:$ssy)<br>");
}

function getplanetatcoords($cx, $cy)
{
    $qr = "select * from `planets` where `coordx`=$cx and `coordy`=$cy LIMIT 1";
    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        return $qres;
    } else {
        return null;
    }
}

function changeplanetowner($pid, $newowner)
{
    getplanetowner($pid, $uid, $uname);
    $qr = "update	`planets` set `ownerid`=$newowner where `pid`=$pid";
    query_exec($qr);
    adddebug("Planet $pid owner changed <br>");
    $plname = getplanetname($pid);
    $uname = getusername($newowner);
    newsysmessage("$uname,Planet $plname now belongs to you", "Planet");
    newsysmessage("Your Planet $plname now belongs to $uname", "Planet", $uid);
}

function getplanetresources($pid, &$plg, &$plm, &$plt)
{
    $qur = "select gold,metalum,tritium from planets where pid=$pid";
//            addbottomoutput($qur);
    executequery($qur, $qrres, $qrcnt);
    if ($qrcnt == 1) {
        $dbarr = query_fetch_array($qrres);
//                adddebugval('gold',$dbarr['gold']);
//                adddebugval('metalum',$dbarr['metalum']);
//                adddebugval('tritium',$dbarr['tritium']);

        $plg = floor($dbarr['gold']);
        $plm = floor($dbarr['metalum']);
        $plt = floor($dbarr['tritium']);
//                adddebugval('gold',$plg);
//                adddebugval('metalum',$plm);
//                adddebugval('tritium',$plt);
    } else {
        $plg = -1;
        $plm = -1;
        $plt = -1;
    }
}

function planetaddresources($pid, $gadd, $madd, $tadd)
{
    getplanetresources($pid, $plg, $plm, $plt);
    if (!($plg > 0)) {
        adddebug('<BR>ERROR. Getting planet resources!!!!<BR>');
        return false;
    }
//                adddebugval('gold',$plg);
//                adddebugval('metalum',$plm);
//                adddebugval('tritium',$plt);
//                adddebugval('goldadd',$gadd);
//                adddebugval('metalumadd',$madd);
//                adddebugval('tritiumadd',$tadd);
    $newg = $plg + $gadd;
    $newm = $plm + $madd;
    $newt = $plt + $tadd;



    $qur = "UPDATE `planets` 
				SET `lastupdate` ='" . mtimetn() . "', 
				`gold` =$newg,
				`metalum` =$newm,			
				`tritium` =$newt 														
			 WHERE `pid` ='" . $pid . "' LIMIT 1;";
    query_exec($qur);

    return true;
}

function getbuildeffectforplanet($uid = null, $onlypid = null, $extrafields, $industry, &$qrcnt)
{
    $extcrit = "";
    if ($uid === null) {
        if ($onlypid != null) {
            getplanetowner($onlypid, $uid, $uname);
            $extcrit = " and `planets`.`pid`=$onlypid";
        } else {
            if (myisset(getsessionvar('id'))) {
                $uid = $_SESSION['id'];
            } else {
                $uid = 0;
            }
        }
    }

    if ($extrafields != '') {
        $extrafields = ',' . $extrafields;
    }
    //adddebugval("USRID",$uid);
    if ($uid > 0) {
        $qr = "select planets.pid,users.techlastupdate,blevel,resefperc,population $extrafields from users,buildings,buildingtypes,planets,planettypes where typeid=ptypeid and planets.pid=buildings.pid and buildings.btid=buildingtypes.btid and planets.ownerid=$uid and users.id=$uid and reseffect like '%$industry%' $extcrit order by pid";
    } else {
        $qr = "select planets.pid,0,blevel,resefperc,population $extrafields from buildings,buildingtypes,planets,planettypes where typeid=ptypeid and planets.pid=buildings.pid and buildings.btid=buildingtypes.btid and planets.ownerid=$uid and reseffect like '%$industry%' $extcrit order by pid";
    }

    executequery($qr, $qrres, $qrcnt);
    //addbottomoutput($qr."<BR>");
    return $qrres;
}

function calcpeoplegrowth($pid)
{//per hour***
    $popmax = calcpeopleaccom($pid);
    //adddebugval('popmax',$popmax);

    $qrres = getbuildeffectforplanet(null, $pid, '', 'popfeed', $qrcnt);
    //adddebugval('cnt=',$qrcnt);
    if ($qrcnt > 0) {
        $pgrowth = 0;
        for ($i = 0; $i < $qrcnt; $i++) {
            $qarr = query_fetch_array($qrres);
            $lvl = $qarr['blevel'];
            // adddebugval('lvl=',$lvl);
            $curpop = $qarr['population'];
            // adddebugval('curpop=',$curpop);
            //$fed=exp($lvl)*1000; //pop which can be fed (BASIC) DELETED not needed
            //here we can calc bonus for fed
            if ($curpop < $popmax) {
                $pgrowth += exp($lvl) / $lvl * sqrt($lvl);
            } else {
                adddebugval('Pop Toped', $popmax);
            }
        }
        return $pgrowth;
    } else {
        return false;
    }
}

function calcpeopleaccom($pid)
{
    $qrres = getbuildeffectforplanet(null, $pid, 'maxpopulation', 'population', $qrcnt);
    if ($qrcnt > 0) {
        $popmax = 0;
        $planetpopmax = 1000000000;
        for ($i = 0; $i < $qrcnt; $i++) {
            $qarr = query_fetch_array($qrres);
            $timelapse = $qarr['techlastupdate'];
            $tmdif = (mtimetn() - $timelapse) / 3600; //time elapsed since last update
            $lvl = $qarr['blevel'];
            $curpop = $qarr['population'];
            $planetmaxpop = $qarr['maxpopulation'];
            $c = round(exp($lvl), 1);
            $d = 1 + pow(1.88, $lvl) / 120;
            $f = (505000 / $d) * $c;
            $popmax = $f;
            //	adddebugval('popmax',$popmax);
        }
        return min($popmax, $planetpopmax);
    } else {
        return false;
    }
}

function isemptysolarsystem($solx, $soly)
{
    adddebugval('checking solsys', "[$solx:$soly]");
    $qr = "select * from planets where solsysx=$solx and solsysy=$soly and ownerid>0";
    return executequery($qr, $qres, $qrcnt) and $qrcnt == 0;
}

function getemptysolarsystem(&$solx, &$soly)
{
    $kk = 2;
    do {
        $k = 0;
        do {
            for ($x = -1; $x < 1; $x++) {
                for ($y = -1; $y < 1; $y++) {
                    $newsx = $solx + $x;
                    $newsx = max($newsx, 0);
                    $newsy = $soly + $y;
                    $newsy = max($newsy, 0);
                    if (isemptysolarsystem($newsx, $newsy)) {
                        $solx = $newsx;
                        $soly = $newsy;
                        return true;
                        break 2;
                    } else {
                        adddebug("[$newsx,$newsy] not empty!!!<br>");
                    }
                }
            }
            $solx += rand(-$kk, $kk);
            $solx = max($solx, 0);
            $soly += rand(-$kk, $kk);
            $soly = max($soly, 0);
        } while ($k++ < 10); // 10 tries
    } while ($kk++ < 10); //10 solar systems away
    return false;
}

function getemptyplanetinsolarsystem($sx, $sy)
{
    global $maxplanets;

    $p = rand(1, $maxplanets - 1); //planets in solar system
    $qres = getsolsysplanets($sx, $sy, $qrcnt);
    for ($i = 0; $i < $p; $i++) { //proceed to record no $p
        $dbarr = query_fetch_array($qres);
    }

    return $dbarr['pid'];
}

function newuser($uid)
{
    global $galaxysize;
    //get a user planet  that already exists
    if (rand(0, 10) > 5) {
        $s = "ASC";
    } else {
        $s = "DESC";
    }
    $qr = "select * from planets where ownerid>0 order by pid $s LIMIT 1";
    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        $dbarr = query_fetch_array($qres);
        $solx = $dbarr['solsysx'];
        $soly = $dbarr['solsysy'];
        if (getemptysolarsystem($solx, $soly)) {
            adddebug("Found an empty solar system!!![$solx,$soly]<br>");
        } else {
            adddebug('ERROR!!!! cant find an empty solar system!!!<br>');
            return ('ERROR!!!! cant find an empty solar system!!!<br>');
        }
    } else { //1st user
        $solx = rand(0, $galaxysize - 1);
        $soly = rand(0, $galaxysize - 1);
    }
    $pid = getemptyplanetinsolarsystem($solx, $soly);
    if ($pid > 0) {
        $qr = "update users set pid=$pid where id=$uid";
        query_exec($qr);
        $qr = "update planets set ownerid=$uid where pid=$pid";
        query_exec($qr);
        adddebug("SUCCESS!!!! user $uid has planet $pid<br>");
    } else {
        adddebug("ERROR!!!! cant find an empty planet in solar system [$solx:$soly] !!!<br>");
        return "ERROR!!!! cant find an empty planet in solar system [$solx:$soly] !!!<br>";
    }
    //echo "Add default tech for new user ID=$uid <BR>";
    $qr = "insert into techuser (TechID,UserID) values(1,$uid)";
    query_exec($qr);
    //echo "Add default Ships for new user ID=$uid <BR>";
    defaultshiptypes($uid);
    //add default resources to planet
    $qr = "update planets set gold=3000,metalum=3000,tritium=3000,population=10000 where pid=$pid";
    query_exec($qr);


    return " User planet is $pid";
}
