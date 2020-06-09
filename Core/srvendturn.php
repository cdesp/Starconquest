<?php

global $onclient, $debugdata;
if (!myisset($onclient)) {
    include_once("../startup.php");
    require_once('myutils.php');
    require_once('planetutils.php');
    require_once('shiputils.php');
    require_once('messages.php');
    require_once('battleutils.php');
    include_once('../lib/codeaid.net_snippet.php');
    db_connect();
} else {
    require_once(MYPATH . '/Core/shiputils.php');
    require_once(MYPATH . '/Core/myutils.php');
    require_once(MYPATH . '/Core/planetutils.php');
    require_once(MYPATH . '/Core/common.php');
    require_once(MYPATH . '/Core/messages.php');
    require_once(MYPATH . '/Core/battleutils.php');
    require_once(MYPATH . '/lib/codeaid.net_snippet.php');
}

//start upgrade from slef resources
function canbuildingupg($pid, $btid)
{
    $gb = true;
    $mb = true;
    $tb = true;

    $quer = "SELECT * FROM `buildings`,`buildingtypes` WHERE `pid`=$pid  and 					`buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=$btid LIMIT 1;";
    if (executequery($quer, $qres, $qrcnt)) {
        $dbarr = query_fetch_array($qres);
        $g = $dbarr['gold']; //in storage
        $m = $dbarr['metalum'];
        $t = $dbarr['tritium'];
        $bgupg = $dbarr['goldupg'];
        $bmupg = $dbarr['metalumupg'];
        $btupg = $dbarr['tritiumupg'];
        $bupglevel = $dbarr['blevel'] + 1;

        //getupgresneeded($bgupg,$bmupg,$btupg,$bupglevel);

        $gb = $g >= $bgupg;
        $mb = $m >= $bmupg;
        $tb = $t >= $btupg;

        if ($gb and $mb and $tb) { //resources are available
            adddebug('resources available<br>');
            $newg = $g - $bgupg;
            $newm = $m - $bmupg;
            $newt = $t - $btupg;
            if ($newm < 0) {
                adddebugval("metal  ", "$m $bmupd");
            }
            if ($newt < 0) {
                adddebugval("trit   ", "$t $btupd");
            }
            //start upgrading
            $tmst = mtimetn();
            $upgdays = $dbarr['daysupg'];
            $upghours = $dbarr['hoursupg'];
            $upgmins = $dbarr['minsupg'];
            $tmdur = maketimetoupg($upgdays, $upghours, $upgmins);
            $tmfinished = $tmst + $tmdur;


            adddebugval('st', date("d : H:i:s", $tmst));
            adddebugval('dur', gettimetoupgrade($tmdur));
            adddebugval('fin', date("d : H:i:s", $tmfinished));
            $qr = "update `buildings` set `baction`=1, `bacttimestart`=$tmst, `bacttimedur`=$tmdur, `bactfinished`=$tmfinished, `gold`=$newg, `metalum`=$newm, `tritium`=$newt where `pid`=$pid and `btid`=$btid LIMIT 1;";
            //	 echo $qr.'<br>';

            query_exec($qr);
        }
    }
    return $gb and $mb and $tb;
}

function startbuildingupg($pid, $btid)
{
    //nothing the upgrade is done in check for efficiency
}

function putrestobuildings($pid, &$goldadd, &$metaladd, &$tritadd, $par = null)
{
    $goldspend = 0;
    $metalspend = 0;
    $tritspend = 0;
    if (!myisset($par)) {
        $parst = "`bparentid` is NULL";
        $pr = 0;
    } else {
        $parst = "`bparentid`=$par";
        $pr = $par;
    }

    $quer = "SELECT * FROM `buildings`,`buildingtypes` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and $parst order by btype";

    if (executequery($quer, $qres, $qrcnt)) {
        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $btid = $dbarr['btid'];
            $goldpart = $dbarr['percnt'] * $goldadd / 100;
            $metalpart = $dbarr['percnt'] * $metaladd / 100;
            $tritpart = $dbarr['percnt'] * $tritadd / 100;
            if (!myisset($par)) {
                $goldspend += $goldpart;
                $metalspend += $metalpart;
                $tritspend += $tritpart;
                putrestobuildings($pid, $goldpart, $metalpart, $tritpart, $btid);
            }
            //adddebug("$goldpart,$metalpart<br>");
            //update database with whatever is left
            $qur = "UPDATE `buildings` 
				SET  
				`gold` =`gold`+$goldpart,
				`metalum` =`metalum`+$metalpart,			
				`tritium` =`tritium`+$tritpart 														
			 WHERE `pid` ='" . $pid . "' and `btid`=$btid LIMIT 1;";
            query_exec($qur);



            //check auto upgrade
            //not any more
//				if ($dbarr['baction']==0){//no upgrade so check
//					if (canbuildingupg($pid,$btid)) {
//						startbuildingupg($pid,$btid);
//					}
//				}
            //else { //check for upgrade end
            //done in seperate function for all buildings
            //$sttm=$dbarr['bacttimestart'];
            //$tmdur=$dbarr['bacttimedur'];
            //if (istimetoupg($sttm,$tmdur)) {
            //	upgradeplanetbuilding($pid,$btid);
            //	}
            //}
        }
        $goldadd -= $goldspend;
        $metaladd -= $metalspend;
        $tritadd -= $tritspend;
    }
}

function checkifbuildingsupgraded()
{
    $tmnow = mtimetn();
    $qr = "SELECT * FROM `buildings`,`buildingtypes` WHERE `buildings`.`btid`=`buildingtypes`.`btid` and `bactfinished`<=$tmnow and `baction`=1  order by `bactfinished`";

    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        for ($i = 0; $i < $qrcnt; $i++) {
            //adddebug("buildings upgraded:$qrcnt<br>");
            $dbarr = query_fetch_array($qres);
            $pid = $dbarr['pid'];
            $btid = $dbarr['btid'];
            upgradeplanetbuilding($pid, $btid);
            $tmfin = $dbarr['bactfinished'];
            $bname = $dbarr['bname'];
            $lvl = $dbarr['blevel'] + 1;
            $pname = getplanetname($pid);
            getplanetowner($pid, $uid, $uname);
            //adddebug("buildings $bname upgraded<br>");
            newsysmessage("building $bname on planet $pname has upgraded to level $lvl", "build", $uid, $tmfin);
        }
    }
}

function calculateproduction($pid = null, &$gold, &$metal, &$trit)
{
    $uid = $_SESSION['id'];
    calculatetechpoints($uid, $pid);
    calculatepopulation($uid, $pid);
    // calculatepopulation(0,null); this one calculates for all free planets


    if ($pid == null) {
        if (!myisset(getsessionvar('id'))) {
            $quer = "SELECT * FROM `planets`";
        }  //get all planets
        else {
            $quer = "SELECT * FROM `planets` where `ownerid`=$uid";
        }  //get session user planets
    } else {
        $quer = "SELECT * FROM `planets` where pid=$pid"; //todo:select only from owners planets
    }


    $qres = query_exec($quer);
    $rowsn = query_num_rows($qres);
    if ($rowsn > 0) {
        // 		 query_exec("SET AUTOCOMMIT=0");
        //		 query_exec("START TRANSACTION");
        adddebug('Updating ' . $rowsn . ' planets<br>');
        // flush();
        for ($i = 0; $i < $rowsn; $i++) {
            $dbarr = query_fetch_array($qres);
            $id = $dbarr['pid'];
            $timelapse = $dbarr['lastupdate'];
            $tmdif = (mtimetn() - $timelapse) / 3600;

            $gp = $dbarr['goldprod']; //production per hour
            $mp = $dbarr['metalumprod']; //production per hour
            $tp = $dbarr['tritiumprod']; //production per hour
            getplanethourprod($id, $gp, $mp, $tp); //add techs and stuff for per hour prod
            $goldadd = $gp * $tmdif; //add according to the time passed
            $metaladd = $mp * $tmdif; //add according to the time passed
            $tritadd = $tp * $tmdif; //add according to the time passed
            //	adddebugval('time',$tmdif);
            //	adddebugval('gp',$gp);
            //	adddebugval('gtotalontime',$goldadd);
            $goldtoadd = $goldadd;
            putrestobuildings($id, $goldadd, $metaladd, $tritadd);
            //	adddebugval('gforplanet0',$goldadd);
            //	adddebugval('gforbuild0',$goldtoadd-$goldadd);
            $qr = "
			SELECT sum(percnt) as p FROM `buildings`,`buildingtypes` WHERE buildings.btid=buildingtypes.btid and bparentid is null and `pid`=$id LIMIT 1;
			";
            if (executequery($qr, $ures, $ucnt) and $ucnt > 0) {
                $uarr = query_fetch_array($ures);
                $sumperc = $uarr['p'];
                $gbuild = ($sumperc / 100) * $gp; //per hour
                $mbuild = ($sumperc / 100) * $mp; //per hour
                $tbuild = ($sumperc / 100) * $tp; //per hour
                $qs = ", goldtobuild=$gbuild, metalumtobuild=$mbuild, tritiumtobuild=$tbuild";
            } else {
                $qs = '';
            }


            //update planet resources with whatever is left
            //adddebugval('PLANET',$id);
            //adddebugval('gforplanet1',$goldadd);
            $gold = $dbarr['gold'] + $goldadd;
            //adddebugval('gforplanet2',$gold);
            $metal = $dbarr['metalum'] + $metaladd;
            $trit = $dbarr['tritium'] + $tritadd;


            //	adddebugval('gforbuild2/hour',$gbuild);
            //	adddebugval('gforplanet2/hour',$gp-$gbuild);
            //	adddebugval('gforbuild2ontime',$gbuild*$tmdif);
            //	adddebugval('gforplanet2ontime',($gp-$gbuild)*$tmdif);
            //	adddebugval('gforplanet',$goldadd);
            //echo "[".$resh."] --> [".$trit."]<br>";
            //PLANET DEFENSES
            $milres = getbuildeffectforplanet(null, $id, '', 'military', $milcnt); //one only
            if ($milcnt > 0) {
                $milarr = query_fetch_array($milres);
                $blevel = $milarr['blevel'];
                $misperhour = $blevel * $blevel;
                $misadd = $misperhour * $tmdif;
                //adddebug("Misiles add=$misadd<BR>");
            } else {
                $misadd = 0;
            }

            $shldres = getbuildeffectforplanet(null, $id, '', 'shield', $shldcnt); //one only
            if ($shldcnt > 0) {
                $shldarr = query_fetch_array($shldres);
                $blevel = $shldarr['blevel'];
                $shield = $blevel * $blevel * 35;
                //adddebug("Shield=$shield<BR>");
            } else {
                $shield = 0;
            }


            $qur = "UPDATE `planets` 
				SET `lastupdate` ='" . mtimetn() . "', 
				`gold` =$gold,
				`metalum` =$metal,			
				`tritium` =$trit,
				`missiles`=`missiles`+$misadd,
				`shield`=$shield			
				 $qs											
			 WHERE `pid` ='" . $id . "' LIMIT 1;";
            query_exec($qur);
            //	addbottomoutput($qur."<BR>");
            //echo $qur.'<br>';
        }
        //   		 query_exec("COMMIT");
        adddebug('...DONE!!<br>');
    }
}

function gettechpointsperhour($population, $perc)
{
    //1% of population produces techpoints
    $poptech = 0.01 * $population;
    $corrupt = (sqrt(sqrt($poptech) / 6) / 100); //percentage
    $tpday = sqrt($poptech);
    $cleantpday = $tpday - ($corrupt * $tpday);
    $cleantpday += $perc * $cleantpday; //add percentage
    //adddebug("TPerDay=$cleantpday<br>");
    return $cleantpday / 24;
}

function gettechpercentage($level, $perc)
{
    return $level * ($level / 8) * $perc;
}

function gettechpoints($population, $perc, $tim)
{
    $tphour = gettechpointsperhour($population, $perc);
    //adddebug("pop:$population, perc:$perc, time:$tim --> $tphour<br>");
    return $tphour * $tim;
}

function calculatetechpoints($uid = null, $onlypid = null)
{
    //	adddebug('-------Tech calc--------<br>');

    $qrres = getbuildeffectforplanet($uid, $onlypid, '', 'techpoints', $qrcnt);
    if ($qrcnt > 0) {
        $newperc = 0;
        $prepid = 0;
        $techp = 0;
        //adddebugval('plan-effects',$qrcnt);
        for ($i = 0; $i < $qrcnt; $i++) {//for each user planet
            $qarr = query_fetch_array($qrres);
            $pid = $qarr['pid'];
            if ($onlypid != null) {
                if ($pid != $onlypid) {
                    continue;
                }
            }
            if ($prepid == 0) {
                $prepid = $pid;
                $timelapse = $qarr['techlastupdate'];
                $tmdif = (mtimetn() - $timelapse) / 3600;
            }
            $level = $qarr['blevel'];
            $effperc = $qarr['resefperc'];
            if ($pid != $prepid) {
                $techp += gettechpoints($population, $newperc, $tmdif);
                $newperc = 0;
            }
            $timelapse = $qarr['techlastupdate'];
            $tmdif = (mtimetn() - $timelapse) / 3600;
            $population = $qarr['population'];
            $newperc += gettechpercentage($level, $effperc);
            //adddebugval(" tech points $i =",$techp);
        }
        if (myisset($uid)) { //if planet belongs to a user
            $techp += gettechpoints($population, $newperc, $tmdif);
            //add tech points to user account
            if ($techp > 0) {
                //adddebugval('Total tech points',$techp);
                $t = mtimetn();
                $qr = "update users set techpoints=techpoints+$techp, techlastupdate=$t where id=$uid";
                // addbottomoutput($qr.'<br>');
                query_exec($qr);
            }
        }
    }
}

function calculatepopulation($uid = null, $onlypid = null)
{
    //adddebug('-------Pop Growth calc--------<br>');

    if ($uid === null) {
        if ($onlypid != null) {
            getplanetowner($onlypid, $uid, $uname);
            $extcrit = " and `planets`.`pid`=$onlypid";
        } else {
            $uid = $_SESSION['id'];
            $extcrit = "";
        }
    }

    $qres = getalluserplanets($uid);
    $qrcnt = query_num_rows($qres);
    //adddebugval('pop planets',$qrcnt);
    for ($i = 0; $i < $qrcnt; $i++) {
        $qarr = query_fetch_array($qres);
        $pid = $qarr['pid'];
        if ($onlypid != null) {
            if ($onlypid != $pid) {
                continue;
            }
        } //skip
                
        //adddebug('------------------------<br>');
            
        //adddebugval('pid',$pid);
        $popgrowth = calcpeoplegrowth($pid); //per hour (is 0 if we reach max pop)
        //adddebugval('popgrowth',$popgrowth);
        $timelapse = $qarr['lastupdate'];
        $tmdif = (mtimetn() - $timelapse) / 3600; //time elapsed since last update
        //	adddebugval('tmdif',$tmdif);
        $timedpopgrowth = $popgrowth * $tmdif;
        //adddebugval('timedpopgrowth',$timedpopgrowth);
        $qr = "update `planets` set `population`=`population`+$timedpopgrowth where `pid`=$pid";
        //addbottomoutput($qr.'<br>');

        query_exec($qr);
    }
}

//todo change this to have a finished time in order to be faster and for all ships of all planets
//build ships one by one
function checkshipbuild3($pid = null)
{
    $tmnow = mtimetn();
    //adddebug("tmnow=$tmnow  <BR>");
    $quer = "select * from (SELECT *,(sttime+shiptime*(buildsofar+1)) as entime FROM `shipbuild`) as a where a.entime<=$tmnow   order by a.entime";
    if (executequery($quer, $qres, $qrcnt)) {
        $newtm = 0;
        //adddebug("checking ship build <BR>");
        //adddebug("qrcnt=$qrcnt  <BR>");
        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $id = $dbarr['sbid'];
            //$q=$dbarr['quantity'];
            $stid = $dbarr['stid'];
            $sttime = $dbarr['sttime'];
            $entime = $dbarr['entime'];
            $shiptime = $dbarr['shiptime'];
            $pid = $dbarr['pid'];
            $t1 = $tmnow - $sttime;
            $t2 = $t1 / $shiptime;
            $q = min(floor($t2), $dbarr['quantity']) - $dbarr['buildsofar'];
            adddebug("adding $q ships type $stid to planet<BR>");
            addshipstoplanet($pid, $q, $stid);
            $buildsofar = $dbarr['buildsofar'] + $q;
            if ($buildsofar == $dbarr['quantity']) {
                adddebug("Deleting shipbuild");
                $qur = "delete from `shipbuild` where `sbid`=$id";
            } else {
                adddebug("Updating shipbuild");
                $qur = "update `shipbuild` set `buildsofar`=$buildsofar  where `sbid`=$id";
            }


            query_exec($qur); //do delete this from queue
            $plnname = getplanetname($pid);
            getplanetowner($pid, $uid, $uname);
            getcoordsfromplanet($pid, $cx, $cy);
            newsysmessage("$q ships are ready on planet $plnname [$cx:$cy] ", "Ships", $uid, $entime);
        }
    }
}

//todo change this to have a finished time in order to be faster and for all ships of all planets
function checkshipbuild2($pid = null)
{
    $tmnow = mtimetn();
    $quer = "select * from (SELECT *,(sttime+durtime) as entime FROM `shipbuild`) as a where a.entime<=$tmnow   order by a.entime";
    if (executequery($quer, $qres, $qrcnt)) {
        $newtm = 0;
        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $id = $dbarr['sbid'];
            $q = $dbarr['quantity'];
            $stid = $dbarr['stid'];
            $entime = $dbarr['entime'];
            $pid = $dbarr['pid'];
            addshipstoplanet($pid, $q, $stid);
            $qur = "delete from `shipbuild` where `sbid`=$id";
            query_exec($qur); //do delete this from queue
            $plnname = getplanetname($pid);
            getplanetowner($pid, $uid, $uname);
            getcoordsfromplanet($pid, $cx, $cy);
            newsysmessage("$q ships are ready on planet $plnname [$cx:$cy] ", "Ships", $uid, $entime);
        }
    }
}

function checkshipbuild($pid = null)
{
    if (!myisset($pid)) {
        $quer = "SELECT * FROM `planets`";  //do all planets
        if (executequery($quer, $qres, $qrcnt)) {
            for ($i = 0; $i < $qrcnt; $i++) {
                $dbarr = query_fetch_array($qres);
                checkshipbuild($dbarr['pid']);
            }
        }
    } else {
        $quer = "SELECT * FROM `shipbuild` where pid=$pid order by sttime";
        if (executequery($quer, $qres, $qrcnt)) {
            $newtm = 0;
            for ($i = 0; $i < $qrcnt; $i++) {
                $dbarr = query_fetch_array($qres);
                $id = $dbarr['sbid'];

                $sttm = $dbarr['sttime'];
                $tmdur = $dbarr['durtime'];
                $q = $dbarr['quantity'];
                $stid = $dbarr['stid'];
                if ($newtm != 0) { //check the next in queue if it is finished too
                    $sttm = $newtm;
                }
                adddebug('--------------------<br>');
                adddebugval('sbid', $id);

                if (isshiptimetoupg($sttm, $tmdur)) {
                    $newtm = $sttm + adddst($tmdur); //this is the time the next queue item will start
                    adddebugval('newtime', $newtm);
                    adddebug("$i Build $q ships of type $stid ");

                    //$stid=$dbarr['stid'];
                    addshipstoplanet($pid, $q, $stid);
                    $qur = "delete from `shipbuild` where `sbid`=$id";
                    query_exec($qur); //do delete this
                    $plnname = getplanetname($pid);
                    getcoordsfromplanet($pid, $cx, $cy);
                    newsysmessage("$q ships are ready on planet $plnname [$cx:$cy] ", "Ships");
                } else {
                    //we can't upgrade anything else
                    if ($newtm != 0) { //if it is a next queue item we must update the db
                        adddebugval('newtime', $newtm);
                        adddebug("$i start building next ship of type $stid");
                        $qur = "update `shipbuild` set `sttime`=$newtm where `sbid`=$id";
                        query_exec($qur); //do delete this
                    }
                    break; //exit loop
                }//else
            }//for
        }//if
    }//else
}

function checkroutesmove($rtid = null)
{
    if (myisset($rtid)) {
        $crit1 = " and `rtid`=$rtid ";
    } else {
        $crit1 = '';
    }

    $tm = mtimetn();
    $tms = date("D, d M Y H:i:s", $tm);
    adddebugval('tm', $tms);

    $quer = "SELECT * FROM `routeways` where `eta`<=$tm  $crit1 order by eta,rtid,wid";

    $prertid = 0;
    if (executequery($quer, $qres, $qrcnt)) {
        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $rtid = $dbarr['rtid'];
            adddebugval('$rtid', $rtid);
            $wid = $dbarr['wid'];
            adddebugval('$wid', $wid);

            // if ($prertid!=$rtid) { //one waypoint at a time
            $newx = $dbarr['wx'];
            $newy = $dbarr['wy'];
            setnewcoordsforroute($rtid, $newx, $newy, $wid);
            $prertid = $rtid;
            //}


            adddebug('------------<br>');
        }
    } else {
        addbottomoutput($quer);
    }
}

function checkbattles()
{
    //return 0;//debug temp disable next round
    $tm = mtimetn();
    //$tms = date("D, d M Y H:i:s",$tm);
    // adddebugval('tm',$tms);

    $quer = "SELECT batid FROM `battles` where `bnxtround`<=$tm and `finished`='N' order by batid";

    if (executequery($quer, $qres, $qrcnt)) {
        for ($i = 0; $i < $qrcnt; $i++) {
            $dbarr = query_fetch_array($qres);
            $batid = $dbarr['batid'];

            donextround($batid);
        }
    }
}

function dogettime()
{
    $st = time();
    $dt = new DateTime("@$st");
    $dat1 = date_format($dt, "H:i:s u");
    adddebug($dat1 . '<br>');
    return $dat1;
}

function dodifftime($tm1, $tm2)
{
    $dat3 = time_diff_conv($tm1, $tm2);
    adddebug($dat3 . '<br>');
    return $dat3;
}

function doendturn($pid = null)
{
    global $plg, $plm, $plt;

    if (myisset(getsessionvar('isajax'))) {
        $isajax = $_SESSION['isajax'];
    } else {
        $isajax = false;
    }
    if ($isajax) {
        adddebug("AJAX in DO END TURN<BR>");
    } else {
        adddebug("NO ajax in DO END TURN<BR>");
    }


    if (!$isajax) {
        adddebug('TIMING DOENDTURN<br>');
        Timer::start();
    }

    //TODO: redo calcproduction and checkshipbuild
    //TODO: calculate food production
    //todo: add population growth based on food production

    if (myisset($pid)) {
        calculateproduction($pid, $plg, $plm, $plt); //calcs techpoints and population growth
        //checkshipbuild($pid);
    }
    if (!isset($onclient) and !isset($isajax)) {
        calculateproduction(null, $plg, $plm, $plt); //calcs techpoints and population growth
    }

    //	    checkshipbuild2(); //check for all planets all ship queues build when all ready
    checkshipbuild3(); //check for all planets all ship queues build one by one
    checkifbuildingsupgraded(); //check for all planets all buildings
    checkroutesmove(); //all routes
    checkbattles(); //all battles
    deletedestroyedfleets(); //

    if (!$isajax) {
        Timer::stop();
        adddebugval('TIME ENDTURN', Timer::get());
    }
}


//DEFAULT PAGE COMMANDS
if (myisset(getsessionvar('isajax'))) {
    $isajax = $_SESSION['isajax'];
} else {
    $isajax = false;
}
if (!myisset($onclient) and !myisset($isajax)) {
    ini_set('max_execution_time', 300);
    doendturn();
    adddebug('OK<br>');
    echo 'DEBUG<BR>' . $debugdata . '<BR><BR>';
    echo 'BOUTPUT<BR>' . $bottomoutput . '<BR>';
    flush();
}
