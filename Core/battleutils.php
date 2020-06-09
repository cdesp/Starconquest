<?php

function getshipatbattleposition($batid, $cx, $cy, $round = 0)
{
    $qr = "select * from `battlefleetships`  where `batid`=$batid and `scoordx`=$cx and `scoordy`=$cy ";
    //   	and `round`=$round";
    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        return $qres;
    } else {
        return false;
    }
}

function getemptybattleposition($batid, $isatck, &$stcx, &$stcy)
{
    global $bgquadrh, $bgquadrv;

    if ($isatck) {
        $xleft = 1;
    } else {
        $xleft = $bgquadrh - 2;
    }

    do {
        $stcx = rand($xleft, $xleft + 2);
        $stcy = rand(1, $bgquadrv);
    } while (getshipatbattleposition($batid, $stcx, $stcy) != false);
}

function getbattlenextround($batid)
{
    $qr = "select max(`round`) as maxround from `battlefleetships` where `batid`=$batid";
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $rndarr = query_fetch_array($qrres);
        return $rndarr['maxround']; //its already there
    } else {
        return 1;
    }
}

function insertbattlefleetships($batid, $fltid, $round, $isatck, $stcx, $stcy)
{
    $fsres = getshipsoffleet($fltid);
    $fscnt = query_num_rows($fsres);
    for ($i = 0; $i < $fscnt; $i++) {
        $fsarr = query_fetch_array($fsres);
        $stid = $fsarr['stid'];
        $quant = $fsarr['quantity'];

        $qr = "insert into battlefleetships (`batid`,`fltid`,`stid`,`quantity`,`scoordx`,`scoordy`,`round`) 
	  				values ($batid,$fltid,$stid,$quant,$stcx,$stcy,$round)";
        // addbottomoutput($qr);
        query_exec($qr);
    }
}

function insertbattlefleet($batid, $fltid, $isatck, $round)
{
    if ($isatck) {
        $attck = 'Y';
    } else {
        $attck = 'N';
    }

    $ownerid = getfleetowner($fltid);
    getemptybattleposition($batid, $isatck, $stcx, $stcy);
    $qr = "insert into battlefleets (`batid`,`fltid`,`attacker`,`round`,`ownerid`,`scoordx`,`scoordy`) 
	  				values ($batid,$fltid,'$attck',$round,$ownerid,$stcx,$stcy)";
    $qres = query_exec($qr);
    // addbottomoutput($qr."<BR>");
    //insert fleet shiptypes to battlefleetships
    insertbattlefleetships($batid, $fltid, $round, $isatck, $stcx, $stcy);
}

function dofleetbattle($fltid, $enemres)
{
    if ($enemres != false) {
        $enmcnt = query_num_rows($enemres);
    } else {
        $enmcnt = 0;
    }

    //adddebug("New Battle for $fltid with enemy fleet count:$enmcnt <BR>");
    newsysmessage("NEW BATTLE Fleet $fltid", "Battle<BR>");

    $qres = getfleetbyid($fltid);
    $dbarr = query_fetch_array($qres);
    $cx = $dbarr['coordx'];
    $cy = $dbarr['coordy'];
    $tm = mtimetn();
    $nxttm = $tm + 90; //in 90 secs is the 1st battle round
    //new battle in battles
    //adddebug("Inserting New battle row...<BR>");
    $batid = gettablenextid('battles', 'batid');
    $qr = "insert into `battles` (`batid`,`bcoordx`,`bcoordy`,`bsttime`,`bnxtround`,`finished`) 
					values ($batid,$cx,$cy,$tm,$nxttm,'N') ";
    query_exec($qr);
    //addbottomoutput($qr."<BR>");
    //new attacking fleet  in battle fleets
    $round = 0; //it is 0 cause it is a new battle
    insertbattlefleet($batid, $fltid, true, $round);
    //Add enemy fleets to battle
    //adddebug("Inserting enemy fleets to battle<BR>");
    for ($i = 0; $i < $enmcnt; $i++) {
        $fltarr = query_fetch_array($enemres);
        $enmfltid = $fltarr['fltid'];
        //new defensive fleet  in battle fleets
        //adddebug("New defense fleet $enmfltid to battle<BR>");
        insertbattlefleet($batid, $enmfltid, false, $round);
    }

    return $batid;
    //		checkbattles();
}

function getbattlefleets($batid, $round, $attacker, &$qrcnt, $all = false)
{
    if (!$all) {
        $crit = "and `destroyed`='N'";
    } else {
        $crit = "";
    }
    $qr = "select * from battlefleets where `attacker`='$attacker' and `batid`=$batid $crit and round=$round order by fltid";
    // addbottomoutput("$qr<BR>");
    if (executequery($qr, $qrres, $qrcnt)) {
        return $qrres;
    } else {
        return false;
        addbottomoutput($qr);
    }
}

function getbattlefleetreport($batid, $round, $attacker = 'Y')
{
    $qr = "select GROUP_CONCAT(batresult,' ') from ( select batresult, bfs.batid from battlefleetships inner join 
                (select * from battlefleets where `attacker`='$attacker' and round=$round and batid=$batid) as bfs
                where  battlefleetships.batid=bfs.batid and battlefleetships.round=bfs.round and battlefleetships.fltid=bfs.fltid 
                order by bfs.fltid) as bat
                group by batid";
    // addbottomoutput("$qr<BR>");
    if (executequery($qr, $qrres, $qrcnt)) {
        $qar = query_fetch_array($qrres);
        $t = $qar[0];
        // adddebug("Report:[$t]<br>");
        return $qar[0];
    } else {
        return false;
        addbottomoutput($qr);
    }
}

function getreportforbattle($batid, &$report)
{
    $qr = "select `batreport` from `battles` where `batid`=$batid";
    if (executequery($qr, $qrres, $qrcnt)) {
        $qar = query_fetch_array($qrres);
        $report = $qar['batreport'];
        return true;
    } else {
        return false;
    }
}

function doplanetbattle($fltid, $pid)
{
    adddebug("TODO:  doplanetbattle <br>");

    getplanetowner($pid, $uid, $uname);
    $plname = getplanetname($pid);
    $myname = getusername();
    newsysmessage("Battle started at Planet $plname owned by $uname", "Battle");
    newsysmessage("$uname, $myname is attacking your Planet $plname. ", "Battle", $uid);

    //do the battle
    getcoordsfromplanet($pid, $cx, $cy);


    //get enemy fleets on planet
    $enmfltres = getenemyfleetsfromcoords($cx, $cy);


    //start the battle
    $batid = dofleetbattle($fltid, $enmfltres);

    //add a planet fleet with ships on planet
    $plshpres = getshipsonplanet($pid, $uid, $plshpcnt);

    $attck = 'N';

    getemptybattleposition($batid, false, $stcx, $stcy);
    $round = 0;
    $plfltid = 0;
    $qr = "insert into battlefleets (`batid`,`fltid`,`attacker`,`round`,`ownerid`,`scoordx`,`scoordy`) 
	  				values ($batid,$plfltid,'$attck',$round,$uid,$stcx,$stcy)";
    $qres = query_exec($qr);

    //insert fleet shiptypes to battlefleetships
    adddebugval('plshpcnt', $plshpcnt);
    for ($i = 0; $i < $plshpcnt; $i++) {
        //  insertbattlefleetships($batid,$fltid,$round,$isatck,$stcx,$stcy);
        $plshparr = query_fetch_array($plshpres);
        $stid = $plshparr['stid'];
        $quant = $plshparr['quantity'];
        adddebugval('stid', $stid);
        adddebugval('quant', $quant);
        $qr = "insert into battlefleetships (`batid`,`fltid`,`stid`,`quantity`,`scoordx`,`scoordy`,`round`) 
	  				values ($batid,$plfltid,$stid,$quant,$stcx,$stcy,$round)";
        addbottomoutput($qr);
        query_exec($qr);
    }
}

function calculateaccuracy($accuracy, $weapdist, $shipdist, $speeddif)
{
    $distpenalty = $accuracy * (($shipdist - 1) / 10); //because accuracy is maximum if the ships are near at distance 1
    $weapondistbonus = $accuracy * (($weapdist - $shipdist) / 10); //because weapon can fire longer than ship distance
    $speeddiff = $accuracy * ($speeddif / 10); //because speed gives or takes an edge
    //TODO: ADD Computers % positive bonus

    return $accuracy - $distpenalty + $weapondistbonus + $speeddiff;
}

//Play the battle between 1 attack ship type and 1 defend ship type
//possible outcomes
//ATTACKER
//1) all ships fired (so this type will fire if survived the defender attack)
//2) some ships didn't fire (because the enemy was obliterated) so the should fire on the next ship type
//DEFENDER
//1) defender loses all ships
//2) defender sustains damage but lives
function dotheshipbattle($batid, $atkarr, &$defarr, $thedist, $round, &$reprt, $shipsleft = 0)
{
    if ($shipsleft > 0) {
        $atkquant = $shipsleft;
    } else {
        $atkquant = $atkarr['quantity'];
    }
    $atkprevkilled = $atkarr['killed']; //killed in previous round
    if ($shipsleft == 0) {//else ships left from previous attack
        $atkquant -= $atkprevkilled;
    }
    //adddebug("ATK PREV KILLED: $atkprevkilled<BR>");
    $atkweapdist1 = $atkarr['wdist1'];
    $atkweapdist2 = $atkarr['wdist2'];
    $atkweapdist3 = $atkarr['wdist3'];
    $atkweapdamg1 = $atkarr['wdmg1'];
    $atkweapdamg2 = $atkarr['wdmg2'];
    $atkweapdamg3 = $atkarr['wdmg3'];
    $atkaccuracy = $atkarr['accuracy'];
    $atkspeed = $atkarr['speed'] / 10; //speed in battle is 1/10 of real speed
    $atkuser = getusername($atkarr['ownerid']);

    $defprevdmg = $defarr['damage'];
    $defquant = $defarr['quantity'];
    $defprevkilled = $defarr['killed']; //killed in previous round
    $defquant -= $defprevkilled;
    $defuser = getusername($defarr['ownerid']);
    adddebug("Defender so far killed: $defprevkilled <BR>");
    if ($defquant == 0) {
        return 0;
    }

    $defshldpower = $defarr['shldpower'];
    $defarmor = $defarr['armor'];
    $defspeed = $defarr['speed'] / 10; //speed in battle is 1/10 of real speed

    adddebugval('atkquant', $atkquant);
    adddebugval('defquant', $defquant);

    //	adddebugval('atkweapdist1',$atkweapdist1);
    //	adddebugval('atkweapdist2',$atkweapdist2);
    //	adddebugval('atkweapdist3',$atkweapdist3);
    //	adddebugval('atkweapdamg1',$atkweapdamg1);
    //	adddebugval('atkweapdamg2',$atkweapdamg2);
    //	adddebugval('atkweapdamg3',$atkweapdamg3);
    //attacker
    $atkpower = 0;
    if ($atkweapdist1 >= $thedist) {
        //	   adddebugval('atkaccuracy',$atkaccuracy);
        $accuracy = calculateaccuracy($atkaccuracy, $atkweapdist1, $thedist, $atkspeed - $defspeed);
        //   	adddebugval('accuracy',$accuracy);
        //	adddebugval('atkweapdamg1',$atkweapdamg1*($accuracy/10));
        $atkpower += $atkweapdamg1 * ($accuracy / 10);
    }
    if ($atkweapdist2 >= $thedist) {
        $accuracy = calculateaccuracy($atkaccuracy, $atkweapdist2, $thedist, $atkspeed - $defspeed);
        //  	adddebugval('accuracy',$accuracy);
        //	adddebugval('atkweapdamg2',$atkweapdamg2*($accuracy/10));

        $atkpower += $atkweapdamg2 * ($accuracy / 10);
    }
    if (($atkweapdist3 != null) and ($atkweapdist3 >= $thedist)) {
        $accuracy = calculateaccuracy($atkaccuracy, $atkweapdist3, $thedist, $atkspeed - $defspeed);
        // 	adddebugval('accuracy',$accuracy);
        //	adddebugval('atkweapdamg3',$atkweapdamg3*($accuracy/10));

        $atkpower += $atkweapdamg3 * ($accuracy / 10);
    }

    adddebugval('atkpower', $atkpower);
    adddebugval('defprevdmg', $defprevdmg);
    //defender
    $defpower = $defshldpower + $defarmor; //total damage defender
    adddebugval('defpower', $defpower);
    //calculations
    $atktotalpower = $atkquant * $atkpower;
    $deftotalpower = $defquant * $defpower - $defprevdmg;
    $batresult = $deftotalpower - $atktotalpower;
    adddebugval('atktotalpower', $atktotalpower);
    adddebugval('deftotalpower', $deftotalpower);
    adddebugval('batresult', $batresult);

    if ($batresult > 0) { //defender lives but with damage
        $defkilled = $defquant - ($batresult / $defpower);
        $damgremain = ($defkilled - floor($defkilled)) * $defpower;
        $defkilled = floor($defkilled);
    } else { //defender obliterated
        $defkilled = $defquant;
        $damgremain = 0;
    }
    //this is positive only if attacker has more power than defender
    $shipsnotfired = -$batresult / $atkpower;
    if ($shipsnotfired > 0) {
        $shipsnotfired = floor($shipsnotfired);
    } else {
        $shipsnotfired = 0;
    }
    adddebugval('shipsnotfired', $shipsnotfired);
    $defshipsremain = $defquant - $defkilled;



    //	$atckcankill=$atktotalpower / $defpower;//ships of defender that the attacker can kill
    //	$defshipsremain=$defquant-$atckcankill;//defender ships that remain intact
    //	$damgremain=$defshipsremain*$defpower;//how much damage the last ship has received
    //	if ($atckcankill>1)
    //   	$shipsnotfired=floor($damgremain/$atkpower);//how many attack ships has not fired
    //	else 	$shipsnotfired=0;
    //	if ($defshipsremain>0){
    //	   $dmgleft=$defshipsremain-floor($defshipsremain);
    //	   if ($dmgleft>0) {
    //		   $damgremain2=$defpower-$dmgleft*$defpower;
    //		   $defshipsremain=floor(++$defshipsremain);	//add one wounded ship
    //	   }
    //	}
    //	adddebugval("atckcankill",$atckcankill);
    //	adddebugval("Attcktotalpower",$atktotalpower);
    //	adddebugval("defpower",$defpower);
    //	adddebugval("damgremain",$damgremain);
    //	adddebugval("defkilled",$defkilled);
    //add to battlerounds
    adddebug('====== BATTLE INFO ======<br>');
    adddebugval('atkquant', $atkquant);
    adddebugval('defquant', $defquant);
    adddebugval("shipsnotfired", $shipsnotfired);
    adddebugval("defshipsremain", $defshipsremain);
    adddebugval("defkilled", $defkilled);
    adddebugval("defdamage", $damgremain);

    $atkfltid = $atkarr['fltid'];
    $atkstid = $atkarr['stid'];
    $deffltid = $defarr['fltid'];
    $defstid = $defarr['stid'];
    //	$round
    //	$batid

    adddebugval('round', $round);

    adddebug('---BATTLE END----<br>');
    adddebug('=====================================<br>');
    //add battle round and update battle fleets
    $allkilled = $defkilled + $defprevkilled; //add the new killed ships
    $qr = "update `battlefleetships` set `killed`=$allkilled,`damage`=$damgremain 
			where `batid`=$batid  and `fltid`=$deffltid and `stid`=$defstid and `round`=$round";
    //addbottomoutput($qr.'<br>');
    query_exec($qr);

    //
    //	adddebugval('shipsnotfired',$shipsnotfired);
    $defarr['killed'] = $allkilled;

    //================User Report===============================
    $accuracy = number_format($accuracy, 2);
    $atkpower = number_format($atkpower, 2);
    $defpower = number_format($defpower, 2);
    $damgremain = number_format($damgremain, 2);
    $atktotalpower = number_format($atktotalpower, 2);
    $deftotalpower = number_format($deftotalpower, 2);
    $atkshiptypename = getshiptypename($atkstid);
    $defshiptypename = getshiptypename($defstid);
    $reprt = "
$atkuser attacks $defuser<BR>
$atkquant [$atkshiptypename] VS $defquant [$defshiptypename]<BR>
Atttacker accuracy is $accuracy <BR>                
$atkshiptypename    |   $defshiptypename <BR>
ATK:$atkpower       |   DEF:$defpower
<BR>
------------[BATTLE RESULT]------------<BR>
POWER: $atktotalpower vs $deftotalpower<BR>                
Defender Ships Destroyed    : $defkilled<BR>    
Defender Ships Survived     : $defshipsremain<BR>        
Defender Ships Damage       : $damgremain<BR>                                       
Attacker Ships left to fire : $shipsnotfired<BR>
<BR>
	";
    return $shipsnotfired;
}

function getshipattackpowerforplanet($shparr)
{
    $atkquant = $shparr['quantity'];
    //	 $atkweapdist1=  $atkarr['wdist1'];
    //	 $atkweapdist2=  $atkarr['wdist2'];
    //	 $atkweapdist3=  $atkarr['wdist3'];
    $atkweapdamg1 = $shparr['wdmg1'];
    $atkweapdamg2 = $shparr['wdmg2'];
    $atkweapdamg3 = $shparr['wdmg3'];
    //	 $atkaccuracy =  $atkarr['accuracy'];
    // 	 $atkspeed	=  $atkarr['speed']/10; //speed in battle is 1/10 of real speed
    $atkpower = 0;
    $atkpower += $atkweapdamg1; //full damage to planet
    $atkpower += $atkweapdamg2;
    $atkpower += $atkweapdamg3;

    //	 adddebugval('$atkquant',$atkquant);
    //	 adddebugval('$atkweapdamg1',$atkweapdamg1);
    //	 adddebugval('$atkweapdamg2',$atkweapdamg2);
    //	 adddebugval('$atkweapdamg3',$atkweapdamg3);
    return $atkquant * $atkpower;
}

function getshipshieldforplanet($shparr)
{
    $quant = $shparr['quantity'];

    $shldpower = $shparr['shldpower'];
    $armor = $shparr['armor'];
    $speed = $shparr['speed'] / 10; //speed in battle is 1/10 of real speed


    return $quant * ($shldpower + $armor) * (1 + $speed / 10);
}

function dotheplanetbattle($batid, $round, $atkres, $atkcnt)
{
    $pid = getplanetinbattle($batid);
    adddebugval('battle planet', $pid);
    if ($pid == 0) {
        return false;
    }
    //get planet defenses
    $qr = "select missiles,shield from planets where `pid`=$pid";
    if (executequery($qr, $qres, $qrcnt)) {
        $plnarr = query_fetch_array($qres);
        $planmissiles = $plnarr['missiles'];
        $planshield = $plnarr['shield'];
    } else {
        adddebug('error getting planet defenses!!!');
        return false;
    }


    $fullpower = 0;
    $fullshield = 0;
    adddebugval('atk fleets', $atkcnt);
    for ($i = 0; $i < $atkcnt; $i++) { //fleets attacking
        $fltarr = query_fetch_array($atkres);
        $fltid = $fltarr['fltid'];
        $uid = $fltarr['ownerid'];
        $fsres = getbattlefleetships($batid, $fltid, $round, $fscnt);
        for ($j = 0; $j < $fscnt; $j++) { //ships attacking
            $fsarr = query_fetch_array($fsres);
            $stid = $fsarr['stid'];
            $shparr = getallshipinfo($stid); //get all ship type info
            $shparr['fltid'] = $fltid;
            $shparr['quantity'] = $fsarr['quantity'];
            $shparr['scoordx'] = $fsarr['scoordx'];
            $shparr['scoordy'] = $fsarr['scoordy'];
            $fullpower += getshipattackpowerforplanet($shparr);
            $fullshield += getshipshieldforplanet($shparr);
        }
    }
    //fullpower is all fleets and ships attack power
    //fullshield is all fleets and ships defense
    $fl = true;
    $atkpower = $fullpower;
    $defpower = $planmissiles;
    adddebugval('fullpower', $fullpower);
    adddebugval('fullshield', $fullshield);
    adddebugval('planshield', $planshield);
    adddebugval('planmissiles', $planmissiles);

    //when there is no shield battle is over
    while ($fl == true) {
        adddebug('-----------------<br>');
        $plans = $planshield;
        $fulls = $fullshield;
        $planshield = floor(max($planshield - $atkpower, 0)); //attacker hit the shield of defender
        $fullshield = floor(max($fullshield - $defpower, 0)); //defender hit the shield of attacker

        if ($plans > 1) {
            $defpower -= ($atkpower / $plans) * $defpower;
        } //loose same percantage of power
        if ($fulls > 1) {
            $atkpower -= ($defpower / $fulls) * $atkpower;
        }

        adddebugval('fullshield', $fullshield);
        adddebugval('planshield', $planshield);
        adddebugval('atkpower', $atkpower);
        adddebugval('defpower', $defpower);


        $fl = $fullshield > 0 and $planshield > 0;
        if ($fl) {
            adddebug('next round<br>');
        }
    }


    if ($fullshield > 0) {//attacker wins
        adddebug('Attacker wins<br>');
        //todo : delete all missiles set planet shield and missile building to level 1
        //set ships remaining according to $fullpower and remaining atkhit
        $survperc = $atkpower / $fullpower; //has the percent of ships survived

        $qr = "update planets set missiles=0,shield=0,ownerid=$uid where pid=$pid;";
        $qr .= "update buildings set blevel=1 where btid=6 or btid=8 and pid=$pid;";
        $qr .= "update battlefleetships set killed=killed+(quantity-killed)*$survperc where batid=$batid and round=$round and fltid in (select fltid from battlefleets where  batid=$batid and round=$round and destroyed='N');";
        query_exec($qr);
    }
    if ($planshield > 0) {//planet wins
        adddebug('Defender wins<br>');
        //todo : delete all fleets
        //set missiles remaining according to $planmissiles and remaining defhit
        $defpower = floor($defpower); //has the missiles left
        $qr = "update battlefleetships set killed=quantity where batid=$batid and round=$round;";
        $qr .= "update battlefleets set destroyed='Y' where batid=$batid and round=$round;";
        $qr .= "update planets set missiles=$defpower where pid=$pid; ";
        $qr .= "";
        query_exec($qr);
    }


    return true;
}

function thedistance($x1, $y1, $x2, $y2)
{
    //	adddebug("x1:y1=[$x1:$y1]<br>");
    //	adddebug("x2:y2=[$x2:$y2]<br>");
    //return 1;//for test
    return sqrt(($x2 - $x1) * ($x2 - $x1) + ($y2 - $y1) * ($y2 - $y1));
}

//an attack fleet of shiptype attacks all the defender fleets(shiptype by shiptype)
//in order to go to the next shiptype the current shiptype must be destroyed.
//for each fleet in defense
//get the ship types which survived
//for each enemy ship type in fleet
//if the attacker weapon distance can reach the ship type
// then
//  get all defender shiptype info ();
//	add (quantity,killed,damage,shipsnotfired(on previous attack))
//  dotheshipbattle and as a result we have ships of this attack type that not fired
// do the same for the next defend ship type.
// and for the next denfender fleet
function attackenemyshipatrange($batid, $atkarr, $sndres, $sndcnt, $round, &$rep)
{
    //	$quantity=  $atkarr['stid'];
    $cx = $atkarr['scoordx'];
    $cy = $atkarr['scoordy'];
    $weapdist = max($atkarr['wdist1'], $atkarr['wdist2'], $atkarr['wdist3']);
    //$shipsnotfired1=$atkarr['quantity'];
    $shipsnotfired1 = 0;
    adddebug("cx:cy=[$cx:$cy]<br>");

    if ($sndcnt > 0) {
        $sndres->data_seek(0);
    } //goto first record
    for ($i = 0; $i < $sndcnt; $i++) {//for each fleet in defense
        $fltarr = query_fetch_array($sndres);
        $fltid = $fltarr['fltid'];
        $qr = "select * from battlefleetships where `round`=$round and `batid`=$batid and `fltid`=$fltid and `quantity`-`killed`>0";
        //	addbottomoutput($qr.'<br>');
        adddebug("check ship distance $sndcnt <br> ");
        if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
            adddebug("we have $qrcnt defending ships on fleet $fltid<br> ");
            for ($j = 0; $j < $qrcnt; $j++) { //for each enemy ship type in fleet
                $defarr = query_fetch_array($qres);
                $stid = $defarr['stid'];
                $ecx = $defarr['scoordx'];
                $ecy = $defarr['scoordy'];
                $quantity = $defarr['quantity'];
                $killed = $defarr['killed'];
                $damage = $defarr['damage'];
                adddebug("ecx:ecy=[$ecx:$ecy]<br>");
                $thedist = thedistance($cx, $cy, $ecx, $ecy);
                adddebugval('battle dist', $thedist);
                adddebugval('weapon dist', $weapdist);
                adddebug("$j ship distance $thedist<br> ");
                if ($thedist <= $weapdist and $quantity > $killed) { //attack ship
                    $defarr = getallshipinfo($stid); //get all ship type info
                    $defarr['fltid'] = $fltid;
                    $defarr['quantity'] = $quantity;
                    $defarr['killed'] = $killed;
                    $defarr['damage'] = $damage;
                    $shipsnotfired1 = dotheshipbattle($batid, $atkarr, $defarr, $thedist, $round, $report, $shipsnotfired1);
                    $rep .= $report;
                    adddebugval('SHIPS LEFT TO FIRE', $shipsnotfired1);
                    if ($shipsnotfired1 == 0) {
                        break;
                    }
                } elseif ($quantity <= $killed) {
                    adddebug('Enemy ship type destroyed. so skip<br>');
                } else {
                    adddebug('ship too far away skip<br>');
                }
            }//for each ship
        } // if exec
        else {
            adddebug("No more enemy ships  fleet $fltid DESTROYED!!!<br>"); //no more enemy ships
        }
        if ($shipsnotfired1 == 0) {
            break;
        }
    } //for each fleet in defense
}

function getbattlefleetships($batid, $fltid, $round, &$qrcnt)
{
    $qr = "select * from battlefleetships where `batid`=$batid and `fltid`=$fltid and `round`=$round";
    //  addbottomoutput($qr.'<br>');
    if (executequery($qr, $qres, $qrcnt)) {
        return $qres;
    } else {
        return false;
    }
}

function updatebattlereport($batid, $fltid, $stid, $round, $battlereport)
{
    $qr = "update `battlefleetships` set `batresult`='$battlereport' where `batid`=$batid and `fltid`=$fltid and `stid`=$stid and `round`=$round";
    query_exec($qr);
}

function getkillsforshiptype($batid, $fltid, $stid, $round)
{
    $qr = "select `killed` from `battlefleetships` where `batid`=$batid and `fltid`=$fltid and `stid`=$stid and `round`=$round";
    //  addbottomoutput($qr.'<br>');
    if (executequery($qr, $qres, $qrcnt)) {
        $qar = query_fetch_array($qres);
        return $qar['killed'];
    } else {
        return false;
    }
}

//for each attacking shiptype in fleet
//get all attacker ship type info
//add quantity amd coords
//and attack all enemy ships at range
function doshipsattack($batid, $fltarr, $round, $sndres, $sndcnt)
{
    $fltid = $fltarr['fltid'];
    //get attack fleetships quantity from previous round
    $qres = getbattlefleetships($batid, $fltid, $round, $qrcnt);
    addbottomoutput("doshipsattack $qrcnt<br>");
    if ($qrcnt > 0) {
        for ($i = 0; $i < $qrcnt; $i++) { //for each attacking shiptype in fleet
            $shparr1 = query_fetch_array($qres);
            $stid = $shparr1['stid'];
            $killed = getkillsforshiptype($batid, $fltid, $stid, $round - 1); //from previous round
            $shparr = getallshipinfo($stid); //get all ship type info
            $shparr['fltid'] = $fltid;
            $shparr['quantity'] = $shparr1['quantity'];
            $shparr['killed'] = $killed;
            $shparr['scoordx'] = $shparr1['scoordx'];
            $shparr['scoordy'] = $shparr1['scoordy'];
            $no = $i + 1;
            adddebug("($no/$qrcnt) attack $stid-->$sndcnt <br> ");
            $battlereport = "============[ ROUND $round ]============<BR>";
            attackenemyshipatrange($batid, $shparr, $sndres, $sndcnt, $round, $battlereport);
            //addbottomoutput($battlereport);
            updatebattlereport($batid, $fltid, $stid, $round, $battlereport);
        }
    }
}

//For each attacker fleet in battle
//attack all the defenders fleets
function dothebattle($batid, $fstres, $sndres, $round)
{
    $fstcnt = query_num_rows($fstres);
    $sndcnt = query_num_rows($sndres);
    //	 adddebugval('atkcnt',$fstcnt);
    //	 adddebugval('defcnt',$sndcnt);
    if ($fstcnt > 0) {
        $fstres->data_seek(0);
    }
    if ($sndcnt > 0) {
        $sndres->data_seek(0);
    }

    for ($i = 0; $i < $fstcnt; $i++) {
        $fltarr = query_fetch_array($fstres);
        //  	adddebugval('atkfltid',$fltarr['fltid']);
        //	adddebug("$i. doshipbattle<br>");
        doshipsattack($batid, $fltarr, $round, $sndres, $sndcnt);
    }
}

function donextround($batid)
{
    adddebug("do next round for batid=$batid<br>");

    $round = getbattlenextround($batid); //get current=previous round in db
    adddebugval('ROUND', $round);
    adddebug("Round is zero based. Round $round is the previous set.<br>");
    $atkres = getbattlefleets($batid, $round, "Y", $atkcnt);   //get the attacker fleets from the previous round
    //test planet
    //	  dotheplanetbattle($batid,$round,$atkres,$atkcnt);
    //	  return 0;


    $defres = getbattlefleets($batid, $round, "N", $defcnt);   //get the defender fleets from the previous round
    if (($atkres == false) or ($defres == false)) {
        adddebug("No attacking or defending fleets [$atkres , $defres]. battle over??<BR>");
        adddebug("SHOULD NOT HAVE COME HERE<BR>");
        $atkwins = ($atkres != false);
        battlefinished($batid, $atkwins);
        return false;
    }

    //  $atkfirst=($round % 2) != 0;//odd means attakcer
    //	  if ($atkfirst) {
    //		   adddebug('first<br>');
    //		   dothebattle($batid,$atkres,$defres,$round);
    //	  }
    //	    else dothebattle($batid,$defres,$atkres,$round);

    dobeforenextturn($batid, $round, $atkres, $defres); //copy fleets in new round and move round++
    adddebug('ATTACKER<br>');
    dothebattle($batid, $atkres, $defres, $round); //attack
    adddebug('DEFENDER<br>');
    dothebattle($batid, $defres, $atkres, $round); //attack

    doafternextturn($batid, $round, $atkres, $defres); //check battle ended and set the time for next round
    deletedestroyedfleets(); //
}

function isbattleforplanet($batid)
{
    return getplanetinbattle($batid) > 0;
}

//check if battle has ended and set the time for next round
function doafternextturn($batid, $round, $atkres, $defres)
{
    docheckbattleend($batid, $round);
    $qr = "select `fltid` from `battlefleets` where `batid`=$batid and `attacker`='Y' and `destroyed`='N' and `round`=$round LIMIT 1";
    $attackerexists = (executequery($qr, $qrres, $qrcnt) and ($qrcnt > 0));
    $qr = "select `fltid` from `battlefleets` where `batid`=$batid and `attacker`='N' and `destroyed`='N' and `round`=$round LIMIT 1";
    $defenderexists = (executequery($qr, $qrres, $qrcnt) and ($qrcnt > 0));
    if (!$attackerexists or!$defenderexists) {
        adddebug('Battle ended<br>');
        //battleended
        $attwins = $attackerexists and!$defenderexists;
        if ($attwins and isbattleforplanet($batid)) {//add a new round for planet fight
            $atkres = getbattlefleets($batid, $round, "Y", $atkcnt); //get attacking fleets again
            dobeforenextturn($batid, $round, $atkres);
            $atkres = getbattlefleets($batid, $round, "Y", $atkcnt); //get attacking fleets again
            $attwins = dotheplanetbattle($batid, $round, $atkres, $atkcnt);
        }
        battlefinished($batid, $attwins);
        return 0;
    } else {
        adddebug('Battle continues<br>');
    }

    //set time for next round;
    $tm = mtimetn();
    $tms = date("d-m-Y H:i:s", $tm);
    //	adddebugval('tmnow',$tms);
    $rounddur = 5 * 60; //5 mins
    $tm += $rounddur;
    $tms = date("d-m-Y H:i:s", $tm);
    //	adddebugval('next round',$tms);


    $tm = 20; //DEBUG:testing next round immediately
    $qr = "update `battles` set `bnxtround`=$tm where `batid`=$batid";
    //	addbottomoutput($qr);
    query_exec($qr);
}

function getshipspeed($stid)
{
    $qr = "select b.power,a.size from x_hulls a, x_propulsions b, shiptypes where
	     hullid=xhullid and propulsionid=xpropid and stid=$stid LIMIT 1";
    addbottomoutput($qr);
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $shparr = query_fetch_array($qrres);
        $size = $shparr['size'];
        $power = $shparr['power'];
        $speed = calculatespeed($power, $size);
        //  adddebugval('SPEED',$speed);
        return $speed;
    } else {
        return false;
    }
}

function getfleetspeed($fltid, $batid = null, $round = null, $fltshipsres = null)
{
    if (!myisset($fltshipsres)) {
        $fltshipsres = getbattlefleetships($batid, $fltid, $round, $shpcnt);
    }
    $fscnt = query_num_rows($fltshipsres);
    if ($fscnt > 0) {
        $speed = 9999;
        for ($i = 0; $i < $fscnt; $i++) {
            $fsarr = query_fetch_array($fltshipsres);
            $stid = $fsarr['stid'];
            $fsarr = getallshipinfo($stid); //shiptype info
            $speed = min($speed, $fsarr['speed']);
        }
    } else {
        $speed = -1;
    }

    return $speed;
}

function getfleetatbattlepos($batid, $bcx, $bcy)
{
    $qr = "select `fltid` from `battlefleets` where `scoordx`=$bcx and `scoordy`=$bcy limit 1";
    if (executequery($qr, $qrres, $qrcnt) and ($qrcnt > 0)) {
        $shparr = query_fetch_array($qrres); //shpres 30/5/2020
        return $shparr['fltid'];
    } else {
        return 0;
    }
}

function getshipatbattlepos($batid, $bcx, $bcy)
{
    $round = getbattlenextround($batid);
    $qr = "select `fltid` from `battlefleetships` where `scoordx`=$bcx and `scoordy`=$bcy and `batid`=$batid and `round`=$round limit 1";
    if (executequery($qr, $qrres, $qrcnt) and ($qrcnt > 0)) {
        $shparr = query_fetch_array($qrres); //shpres 30/5/2020
        return $shparr['stid'];
    } else {
        return 0;
    }
}

function findbattlefreepos($batid, &$scx, &$scy)
{
    for ($xi = -1; $xi < 2; $xi++) {
        for ($yi = -1; $yi < 2; $yi++) {
            $sx = $scx + $xi;
            $sy = $scy + $yi;
            $stid = getfleetatbattlepos($batid, $sx, $sy);
            if ($stid == false) {
                $scx = $sx;
                $scy = $sy;
                return 0;
            }
        }
    }
}

function starttheturn($batid, $round, $qres, $isatck)
{
    global $redzonex, $redzonewidth;

    if ($qres == false) {
        return 0;
    }
    adddebug('Starttheturn');
    $qrcnt = query_num_rows($qres);
    if ($qrcnt > 0) {
        $qres->data_seek(0);
    }
    if ($isatck) {
        $ml = 1;
    } else {
        $ml = -1;
    }

    adddebugval('moving ships:', $qrcnt);
    for ($i = 0; $i < $qrcnt; $i++) {
        $fltarr = query_fetch_array($qres);
        $fltid = $fltarr['fltid'];

        //	$shpres=getbattlefleetships($batid,$fltid,$round,$shpcnt);
        //    for ($j=0;$j<$shpcnt;$j++){
        //		$shparr=query_fetch_array($shpres);
        //$stid=$shparr['stid'];
        $movcx = $fltarr['cmdmovcx'];
        $movcy = $fltarr['cmdmovcy'];
        $scx = $fltarr['scoordx'];
        $scy = $fltarr['scoordy'];
        //	$redzxlow=$redzonex-$redzonewidth+1;
        //	$redzxhigh=$redzonex+$redzonewidth;
        //	adddebugval('redzone',$redzonex);
        //	adddebugval('redzonelow',$redzxlow);
        //	adddebugval('redzonehigh',$redzxhigh);
        //	if ($scx<$redzxlow or $scx>$redzxhigh) { //inside the redzone no move on x-axis

        $shipspeed = floor(getfleetspeed($fltid, $batid, $round) / 10);
        $shipspeed = max(1, $shipspeed);
        //	adddebugval('battlespeed',$shipspeed);
        //		if (($movcx==0) and ($movcy==0)) {//default move
        //	  		$movcx=$scx+$shipspeed*$ml;
        //		}
        $crit = '';
        if (($movcx != 0) and ($movcy != 0)) {//move
            //	   adddebugval('scx:scy',"$scx:$scy");
            //	   adddebugval('movcx:movcy',"$movcx:$movcy");
            $path = get_line($scx, $scy, $movcx, $movcy);
            //	   adddebugval('path steps',count($path));
            //	   adddebugval('fleetspeed',$shipspeed);
            $pathi = min(count($path) - 1, $shipspeed);
            //	   adddebugval('i',$pathi);
            $scx = $path[$pathi]['x'];
            $scy = $path[$pathi]['y'];
            //	   adddebugval('new scx:scy',"$scx:$scy");
            if (($scx == $movcx) and ($scy == $movcy)) {//we reached dest
                $crit = ", cmdmovcx=0, cmdmovcy=0 "; //reset move command
            }
            $newfltid = getfleetatbattlepos($batid, $scx, $scy);
            if ($newfltid != false) {
                findbattlefreepos($batid, $scx, $scy);
            }
        }

        //	}
        adddebugval('newcoords', "$scx:$scy");
        $qr = "update battlefleets set scoordx=$scx, scoordy=$scy $crit where batid=$batid and fltid=$fltid  and round=$round";
        // addbottomoutput($qr);
        query_exec($qr);
        $qr = "update battlefleetships set scoordx=$scx, scoordy=$scy $crit where batid=$batid and fltid=$fltid  and round=$round";
        //addbottomoutput($qr);
        query_exec($qr);


        //}//forj
    }//for i
}

function preparetheturn($batid, $newround, $qrres)
{
    adddebug('Prepare turn<BR>');

    $round = $newround - 1;
    $checkbattleend = false;
    if (is_null($qrres)) {
        return;
    }
    if ($qrres == false) {
        return;
    }

    $qrcnt = query_num_rows($qrres);
    adddebugval("FLEETS", $qrcnt);
    if ($qrcnt > 0) {
        $qrres->data_seek(0);
    }

    for ($i = 0; $i < $qrcnt; $i++) {
        $qrarr = query_fetch_array($qrres);
        if ($qrarr['destroyed'] == 'Y') {
            continue;
        }
        $fltid = $qrarr['fltid'];
        $scx = $qrarr['scoordx'];
        $scy = $qrarr['scoordy'];
        //		$ownerid=getfleetowner($fltid);
        $ownerid = $qrarr['ownerid'];
        $bfsres = getbattlefleetships($batid, $fltid, $round, $bfscnt);
        $fleetatnextround = false;
        for ($j = 0; $j < $bfscnt; $j++) {
            //	adddebugval("FLEETShips",$bfscnt);
            $bfsarr = query_fetch_array($bfsres);
            $stid = $bfsarr['stid'];
            $quant = $bfsarr['quantity'];
            $damg = $bfsarr['damage'];
            $killed = $bfsarr['killed'];

            if ($killed == $quant) {
                adddebug("$quant of your ships type $stid in fleet $fltid has been destroyed in battle at round $round");
                newsysmessage("$quant of your ships type $stid in fleet $fltid has been destroyed in battle at round 	$round", "BATTLE", $ownerid);
                $checkbattleend = true;
            } else {//add shiptype for next round
                $fleetatnextround = true;
                $qr = "insert into `battlefleetships` (`batid`, `fltid`, `stid`, `quantity`, `scoordx`, `scoordy`, `round`, `damage`, `killed`) values ($batid,$fltid,$stid,$quant,$scx,$scy,$newround,$damg,$killed) ";
                addbottomoutput($qr . '<br>');
                query_exec($qr);
            }
        }//for j
        if ($fleetatnextround) {
            $destroyed = "N";
        } else {
            $destroyed = "Y";
        }
        $cmdmovcx = $qrarr['cmdmovcx'];
        $cmdmovcy = $qrarr['cmdmovcy'];
        $aimfltid = $qrarr['aimfltid'];
        $attacker = $qrarr['attacker'];
        $qr = "insert into `battlefleets` (`batid`, `fltid`, `attacker`, `scoordx`, `scoordy`, `round`, `destroyed`, `ownerid`, `cmdmovcx`, `cmdmovcy`,`aimfltid`) values ($batid, $fltid, '$attacker', $scx, $scy, $newround, '$destroyed', $ownerid, $cmdmovcx, $cmdmovcy, $aimfltid) ";
        addbottomoutput($qr . '<br>');
        query_exec($qr);
    }

    if ($checkbattleend) {
        docheckbattleend($batid, $round);
    }
}

function docheckbattleend($batid, $round)
{
    //check the fleets and mark them destroyed
    //if all fleets of a player is destroyed then end the battle
    //select the destroyed fleets those that are  quantity=killed for this round only
    $qr = "select `fltid` from `battlefleetships` where `fltid` not in (
			select `fltid` from `battlefleetships` where `quantity`<>`killed` and `batid`=$batid and round=$round)
                        and `batid`=$batid and round=$round
                            group by `fltid`
		";
    addbottomoutput($qr . '<br>'); //the fleets the cotinue to exists
    $qr = "update `battlefleets` set `destroyed`='Y' where `fltid` in ( $qr
		 ) and `destroyed`<>'Y' and `round`=$round";

    addbottomoutput($qr . '<br>');
    query_exec($qr);
}

function dobeforenextturn($batid, &$round, $atkres, $defres = null)
{
    $round++; //new turn
    //copy fleets in next turn  round-1 --> round
    preparetheturn($batid, $round, $atkres);
    if ($defres != null) {
        preparetheturn($batid, $round, $defres);
    }

    //move the ships in new round
    starttheturn($batid, $round, $atkres, true);
    if ($defres != null) {
        starttheturn($batid, $round, $defres, false);
    }
}

function getplanetinbattle($batid)
{
    $qr = "select `pid` from `planets`,`battles` where `coordx`=`bcoordx` and `coordy`=`bcoordy` and `batid`=$batid ";
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $qrarr = query_fetch_array($qrres);
        $pid = $qrarr['pid'];
    } else {
        $pid = 0;
    }

    return $pid;
}

function deletedestroyedfleets()
{
    global $mysqli;
    //delete destroyed fleets and ships
    $qr1 = "select `fltid` FROM `battlefleets` where destroyed='Y'";
    $qr2 = " delete from `fleetships` where (`fltid` in ($qr1)) or (`quantity`=0);";  //maybe quantity=killed here
    $qr3 = " delete from `fleets` where `fltid` in ($qr1);";
    //addbottomoutput($qr2);
    if (!query_exec($qr2)) {
        adddebug("ERROR. CANNOT DELETE FLEETS<BR>");
        adddebug($mysqli->error);
    }
    //addbottomoutput($qr3);
    if (!query_exec($qr3)) {
        adddebug("ERROR. CANNOT DELETE FLEETS<BR>");
        adddebug($mysqli->error);
    }
}

function savefinalbattlereport($batid, $rep)
{
    $qr = "update `battles` set `batreport`='$rep' where `batid`=$batid";
    query_exec($qr);
}

function makefinalreportforbattle($batid, $attackerwins)
{
    //get data for all rounds
    $qr = " 
    SELECT battlefleetships.batid,battlefleetships.fltid,battlefleetships.stid,battlefleetships.round,quantity-killed as noships,damage,killed,attacker,battlefleets.ownerid,stypename,name
    FROM `battlefleetships` 
    JOIN `battlefleets` on battlefleetships.fltid=battlefleets.fltid and battlefleetships.batid=battlefleets.batid 
    JOIN shiptypes on battlefleetships.stid=shiptypes.stid
    JOIN users on battlefleets.ownerid=users.id 
    WHERE battlefleetships.batid=$batid 
    GROUP BY battlefleetships.fltid,battlefleetships.stid,battlefleetships.round 
    ORDER BY battlefleetships.round,attacker DESC,battlefleetships.fltid,battlefleetships.stid        
    ";
    if (!(executequery($qr, $qres, $qrcnt) && $qrcnt > 0)) {
        adddebug("FINAL REPORT ERROR ON DATA RETREIVAL!!!<BR>");
        addbottomoutput($qr . "<BR>");
        return false;
    }
    $round = 0;
    $starr = array();
//    adddebug("DATA CNT $qrcnt<BR>");
    for ($i = 0; $i < $qrcnt; $i++) {
        $batarr = query_fetch_array($qres);
        //process a round
        $fltid = $batarr['fltid'];
        $stid = $batarr['stid'];
        $curround = $batarr['round'];
        $arname = $fltid . '_' . $stid;
        if (!(array_key_exists("$arname", $starr))) {
            $starr["$arname"] = array();
            $starr["$arname"]["fltid"] = $fltid;
            $starr["$arname"]["stid"] = $stid;
            $starr["$arname"]["noships"] = $batarr['noships'];
            $starr["$arname"]["stypename"] = $batarr['stypename'];
            $starr["$arname"]["user"] = $batarr['name'];
            $starr["$arname"]["attacker"] = $batarr['attacker'];
        }
        $starr["$arname"]["killed"] = $batarr['killed'];
    }
//    $tt=print_r ($starr,true);
//    addbottomoutput("starr=$tt<BR>");
    $finalarr = array();
    foreach ($starr as $key => $value) {
        $stid = $value["stid"];
//      adddebug("STID=$stid<BR>");
//      $tt=print_r ($value,true);
//      addbottomoutput("VALUE=$tt<BR>");
        if (!(array_key_exists("$stid", $finalarr))) {
            $finalarr["$stid"] = array();
            $finalarr["$stid"]["stid"] = $stid;
            $finalarr["$stid"]["noships"] = 0;
            $finalarr["$stid"]["stypename"] = $value['stypename'];
            $finalarr["$stid"]["user"] = $value['user'];
            $finalarr["$stid"]["attacker"] = $value['attacker'];
            $finalarr["$stid"]["killed"] = 0;
            if ($value['attacker'] == 'Y') {
                $atkuser = $value['user'];
            } else {
                $defuser = $value['user'];
            }
        }
        $finalarr["$stid"]["noships"] += intval($value['noships']);
        $finalarr["$stid"]["killed"] += intval($value['killed']);
    }

    $atkrep = str_pad("[ATTACKER]", 50, "=", STR_PAD_BOTH) . "<BR>";
    $defrep = str_pad("[DEFENDER]", 50, "=", STR_PAD_BOTH) . "<BR>";
    $atki = 0;
    $defi = 0;
    foreach ($finalarr as $key => $value) {
        $stypename = $value['stypename'];
        $attacker = $value['attacker'];
        $user = $value['user'];
        $noships = $value['noships'];
        $killed = $value['killed'];

        $stid = $value["stid"];
        // adddebug("finar STID=$stid<BR>");
        //$tt=print_r ($value,true);
        //addbottomoutput("finalarr=$tt<BR>");
        $t1 = str_pad("-", 50, "-", STR_PAD_BOTH) . "<BR>";
        $repline = "";
        $repheader = $t1 . "      DESIGN           SHIPS      KILLED     LEFT<BR>" . $t1;
        if ($attacker == 'Y') {
            if ($atki == 0) {
                $repline .= "ATTACKER: $user <BR>";
                $repline .= $repheader;
                $atkuser = $user;
            }
        } else {
            if ($defi == 0) {
                $repline .= "DEFENDER: $user <BR>";
                $repline .= $repheader;
                $defuser = $user;
            }
        }
        $left = $noships - $killed;
        $stpnm = str_pad($stypename, 17, " ", STR_PAD_BOTH);
        $ns = str_pad($noships, 6, " ", STR_PAD_LEFT);
        $kl = str_pad($killed, 6, " ", STR_PAD_LEFT);
        $lf = str_pad($left, 6, " ", STR_PAD_LEFT);
        $repline .= "$stpnm     $ns      $kl    $lf<BR>";

        if ($attacker == 'Y') {
            $atkrep .= $repline;
            $atki++;
        } else {
            $defrep .= $repline;
            $defi++;
        }
    }
    $header = "<BR>" . str_pad("BATTLE $atkuser vs $defuser", 50, ' ', STR_PAD_BOTH);

    if ($attackerwins) {
        $winner = "ATTACKER $atkuser";
    } else {
        $winner = "DEFENDER $defuser";
    }
    $geninfo = str_pad("[BATTLE RESULT]", 50, "=", STR_PAD_BOTH) . "<BR>"
            . str_pad("Battle ended on round $curround", 50, " ", STR_PAD_BOTH) . "<BR>"
            . str_pad("Winner is $winner", 50, " ", STR_PAD_BOTH) . "<BR><BR>";

    $report = "<PRE>" . $header . "</PRE><BR>" .
            "<PRE>" . $atkrep . "</PRE><BR>" .
            "<PRE>" . $defrep . "</PRE><BR>" .
            "<PRE>" . $geninfo . "</PRE><BR>";
    //addbottomoutput($report);
    savefinalbattlereport($batid, $report);

    //TODO: Put the reports to the database on table battle
    return true;
}

function battlefinished($batid, $attackerwins)
{
    adddebug("Battle $batid has finished <br>");
    if ($attackerwins) {
        adddebug("Attacker wins <br>");
    } else {
        adddebug("Defender wins <br>");
    }
    $qr = "update `battles` set finished='Y' where `batid`=$batid ";
    query_exec($qr);

    $pid = getplanetinbattle($batid);

    //get the last round of each fleet with kills
    $qr = "select * from (
		SELECT * FROM `battlefleetships` WHERE killed>0 order by round desc, killed desc ) a  
		where fltid not in (SELECT fltid FROM `battlefleets` where destroyed='Y') and `batid`=$batid
		group by batid,fltid,stid";
    //addbottomoutput($qr);
    if (executequery($qr, $qrres, $qrcnt)) {
        for ($i = 0; $i < $qrcnt; $i++) {
            $qrarr = query_fetch_array($qrres);
            $shipsleft = $qrarr['quantity'] - $qrarr['killed'];
            $fltid = $qrarr['fltid'];
            $stid = $qrarr['stid'];
            adddebug("$shipsleft from stid=$stid<br>");
            adddebugval("fltid", $fltid);
            adddebugval("stid", $stid);
            if ($fltid != 0) {
                $qr = "update `fleetships` set `quantity`=$shipsleft where `fltid`=$fltid and `stid`=$stid";
            } else {
                adddebugval("pid", $pid);
                $qr = "update `ships` set `quantity`=$shipsleft where `pid`=$pid and `stid`=$stid";
            }
            if (!query_exec($qr)) {
                adddebug("ERROR. CANNOT UPDATE SHIPS<BR>");
            }
        }
    }
    deletedestroyedfleets();
    makefinalreportforbattle($batid, $attackerwins);
    //todo:get all participants and inform them
    //newsysmessage("NEW BATTLE Fleet $fltid","Battle");
}

function getuserbattles(&$qrcnt, $userid = null)
{
    if ($userid == null) {
        $userid = $_SESSION['id'];
    }
    $qr = "
			select * from battles where batid in (select batid from battlefleets where ownerid=$userid) order by finished, bsttime
		";
    //addbottomoutput($qr);
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        return $qrres;
    } else {
        return false;
    }
}

function getbattlefleetnumber($batid, $atker)
{
    $qr = "select fltid as cnt from battlefleets where attacker='$atker' and batid=$batid and round=0";
    executequery($qr, $qrres, $qrcnt);
    return $qrcnt;
}

function setbattlefleettomove($batid, $fltid, $x, $y)
{
    $round = getbattlenextround($batid);
    $qr = "update `battlefleets` set `cmdmovcx`=$x, `cmdmovcy`=$y where `batid`=$batid and `fltid`=$fltid and `round`=$round";
    query_exec($qr);
    //return $qr;
}

function getbattleatcoords($cx, $cy)
{
    $qr = "select batid from battles where `bcoordx`=$cx and `bcoordy`=$cy and finished='N' ";
    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        $qrarr = query_fetch_array($qres);
        return $qrarr['batid'];
    } else {
        return false;
    }
}

function isfleetinbattle($fltid)
{
    $qr = "select fltid from battlefleets,battles where battles.batid=battlefleets.batid and fltid=$fltid and finished='N' limit 1";
    if (executequery($qr, $qres, $qrcnt) and $qrcnt > 0) {
        return true;
    } else {
        return false;
    }
}
