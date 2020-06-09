<?php

include_once "galaxyutils.php";

$quer = "
			SELECT * FROM `shiptypes`
		     
			   LEFT JOIN x_weapons xw1	ON `weapon1id`=xw1.`xweaponid`  
			   LEFT JOIN x_weapons xw2	ON `weapon2id`=xw2.`xweaponid`  
			   LEFT JOIN x_weapons xw3	ON `weapon3id`=xw3.`xweaponid` 
			   JOIN x_propulsions ON `propulsionid`=`xpropid`
			   JOIN `x_computers` ON `computerid`=`xcompid`
			   JOIN `x_hulls` ON `hullid`=`xhullid`
			   JOIN `x_sensors` ON `sensorid`=`xsensid`
			   JOIN `x_shields` ON `shieldid`=`xshieldid`
		;
		";

function buildnewship()
{
    $userid = $_SESSION['id'];

    //check ship size;

    $shipname = filter_input(INPUT_POST, 'shipname');
    adddebugval('Shipname', $shipname);

    $hullid = filter_input(INPUT_POST, 'hullsel');
    $compid = filter_input(INPUT_POST, 'compsel');
    $propid = filter_input(INPUT_POST, 'propsel');
    $sensid = filter_input(INPUT_POST, 'sensorsel');
    $shldid = filter_input(INPUT_POST, 'shieldsel');
    $weapid1 = filter_input(INPUT_POST, 'weapon1sel');
    $weapid2 = filter_input(INPUT_POST, 'weapon2sel');
    $weapid3 = filter_input(INPUT_POST, 'weapon3sel');

    adddebugval('hullid', $hullid);
    adddebugval('compid', $compid);
    adddebugval('propid', $propid);
    adddebugval('sensid', $sensid);
    adddebugval('shldid', $shldid);
    adddebugval('weapid1', $weapid1);
    adddebugval('weapid2', $weapid2);
    adddebugval('weapid3', $weapid3);

    $stid = gettablenextid('shiptypes', 'stid');
    $qr = "insert into `shiptypes`  (stid,stypename,propulsionid,computerid,hullid,sensorid,weapon1id,weapon2id,weapon3id,shieldid,ownerid) 
			     values ($stid,'$shipname',$propid,$compid,$hullid,$sensid,$weapid1,$weapid2,$weapid3,$shldid,$userid)";
    query_exec($qr);
    $_SESSION['shipsel'] = $stid;
    $shipsel = $stid;
}

function deleteship($stid)
{
    //todo check if ships are already created;
    $candelete = true;

    //delete all ships from planets fleets etc
    //get all fleets that contain that type of ships
    $qr = "select `fltid` from `fleetships` where `stid`=$stid";
    addbottomoutput($qr . '<BR>');
    if (executequery($qr, $fleetres, $fleetcnt) and $fleetcnt > 0) {
        // adddebug("$fleetcnt Fleets with that ship type found<BR>");
    }
    //delete all ships on fleet
    //TODO: Not delete if shiptype in current battle
    //see what happens when fleet gets deleted and was in battle
    $qr = "delete from `fleetships` where `stid`=$stid";
    query_exec($qr);
    //check if we have an empty fleet
    for ($i = 0; $i < $fleetcnt; $i++) {
        $fltidarr = query_fetch_array($fleetres);
        $fltid = $fltidarr['fltid'];
        $qr = "select `fltid` from `fleetships` where `fltid`=$fltid";
        // addbottomoutput($qr.'<BR>');
        // adddebug("$fltid check for deletion<BR>");
        if (executequery($qr, $qres, $qrcnt) && $qrcnt == 0) {
            //delete empty fleet fltid because no more ships in it
            //   adddebug("deleting fleet $fltid<br>");
            $qr = "delete from `fleets` where `fltid`=$fltid";
            // addbottomoutput($qr.'<BR>');
            query_exec($qr);
        }
    }
    $qr = "delete from `ships` where `stid`=$stid";
    query_exec($qr);
    $qr = "delete from `shipbuild` where `stid`=$stid";
    query_exec($qr);
    //should always delete
    $qr1 = "select `fltid`,`stid` from `fleetships` where `stid`=$stid LIMIT 1";
    if (executequery($qr1, $qres, $qrcnt) and $qrcnt > 0) {
        adddebug('we already have that ship in a fleet<br>');

        $candelete = false;
    }
    $qr2 = "select `stid` from `ships` where `stid`=$stid LIMIT 1";
    if (executequery($qr2, $qres, $qrcnt) and $qrcnt > 0) {
        adddebug('we already have that ship on a planet<br>');
        $candelete = false;
    }
    $qr3 = "select `stid` from `shipbuild` where `stid`=$stid LIMIT 1";
    if (executequery($qr3, $qres, $qrcnt) and $qrcnt > 0) {
        adddebug('we are building that ship on a planet<br>');
        $candelete = false;
    }
    if ($candelete) {
        $qr = "delete from `shiptypes` where `stid`=$stid";
        query_exec($qr);
        adddebug('Ship Desing deleted <br>');
        $_SESSION['shipsel'] = null;
    } else {
        adddebug('<b>Ship cannot be deleted!!!</b> <br>');
    }
}
