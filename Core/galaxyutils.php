<?php

//echo $_SERVER["DOCUMENT_ROOT"];
include_once(dirname(__FILE__) . "/../startup.php");
include_once "shiputils.php";
include_once "planetutils.php";
include_once 'myutils.php';

db_connect();

//	global $galaxysize,$solsyssize,$maxplanets,$solsyscover;
//	global $userid;


function initgalaxy()
{
    global $galaxysize, $solsyssize, $maxplanets, $solsyscover;

    if (!myisset(getsessionvar('galaxysize'))) {
        $quer = "SELECT * FROM `world` ORDER BY  `wid` DESC LIMIT 1";
        $qres = query_exec($quer);
        if (query_num_rows($qres) > 0) {
            $dbarr = query_fetch_array($qres);
            $solsyssize = $dbarr['solsyssize']; //size of solar system fixed 10 means 10x10
            $solsyscover = $dbarr['solsyscover']; //how many planets on a solar system percentage 10 mean 10% so on a 10x10 we have 10 planets
            $galaxysize = $dbarr['galaxysize']; //means the galaxy has 10x10 solar systems
            $maxplanets = $dbarr['maxplanets']; //max planets per solar system

            $_SESSION['galaxysize'] = $galaxysize;
            $_SESSION['solsyssize'] = $solsyssize;
            $_SESSION['solsyscover'] = $solsyscover;
            $_SESSION['maxplanets'] = $maxplanets;
        }
    } else {
        $galaxysize = $_SESSION['galaxysize'];
        $solsyssize = $_SESSION['solsyssize'];
        $solsyscover = $_SESSION['solsyscover'];
        $maxplanets = $_SESSION['maxplanets'];
    }
    //echo $galaxysize."GALAXY INIT<br>";
}

function getusercapitol($usrid = null)
{
    if (!myisset($usrid)) {
        $usrid = $_SESSION['id'];
    }
    $qur = "select `pid` from `users` where `id`=$usrid";
    $qres = query_exec($qur);
    if (query_num_rows($qres) > 0) {
        $trows = query_fetch_array($qres);
        return $trows['pid'];
    } else {
        adddebug("error getting capitol city: usrid=$usrid");
    }
}

function getuserdatabyid($uid = null)
{
    if (!myisset($uid)) {
        $uid = $_SESSION['id'];
    }
    $qur = "select * from `users` where `id`=$uid";
    $qres = query_exec($qur);
    return query_fetch_array($qres);
}

function getusername($uid = null)
{
    $userid = $_SESSION['id'];

    if (!myisset($uid)) {
        $uid = $userid;
    }

    $qur = "select name from `users` where `id`=$uid";
    $qres = query_exec($qur);
    $usrarr = query_fetch_array($qres);
    return $usrarr['name'];
}

function getusertechpoints($uid = null)
{
    $userid = $_SESSION['id'];

    if (!myisset($uid)) {
        $uid = $userid;
    }

    $qur = "select techpoints from `users` where `id`=$uid";
    $qres = query_exec($qur);
    $usrarr = query_fetch_array($qres);
    return $usrarr['techpoints'];
}

function getvisiblemap(&$tx, &$ty, &$bx, &$by)
{
    global $galaxysize;
    $ssx = $_SESSION['ssx'];
    $ssy = $_SESSION['ssy'];

    $adnx = 0;
    $adny = 0;
    if (($ssx - 1) >= 0) {
        $tx = $ssx - 1;
    } else {
        $tx = 0;
        $adnx = 1;
    }
    if (($ssy - 1) >= 0) {
        $ty = $ssy - 1;
    } else {
        $ty = 0;
        $adny = 1;
    }
    if (($ssx + 1) < $galaxysize) {
        $bx = $ssx + 1 + $adnx;
    } else {
        $bx = $galaxysize - 1;
    }
    if (($ssy + 1) < $galaxysize) {
        $by = $ssy + 1 + $adny;
    } else {
        $by = $galaxysize - 1;
    }
}

//----------------------------------------------------


function isquadrfilled($cx, $cy)
{
    $quer = 'SELECT `pid` FROM planets WHERE (`coordx`=' . $cx . ') AND (`coordy`=' . $cy . ') LIMIT 1';
    $qres = query_exec($quer);
    $qrcnt = query_num_rows($qres);

    return $qrcnt != 0;
}

function fillsolsys($solsysx, $solsysy)
{
    global $galaxysize, $solsyssize, $maxplanets, $solsyscover;

    $covi = 0; //planet counter
    $solstx = $solsysx * $solsyssize;
    $solsty = $solsysy * $solsyssize;
    $px = 0;
    $py = 0; //planetx planety

    echo 'Filling Solar system ' . $solsysx . ',' . $solsysy;
    $lastupd = mtimetn(); //world creation time
    while ($covi < $maxplanets) {
        do {
            $px = rand($solstx, $solstx + $solsyssize - 1);
            $py = rand($solsty, $solsty + $solsyssize - 1);
        } while (isquadrfilled($px, $py));
        $covi++;
        $plname = getnewplanetname();
        $nid = gettablenextid('planets', 'pid');
        $pltyp = rand(1, 3);
        $goldprod = rand(20, 200 + $pltyp * 100);
        $metalumprod = rand(10, 100 + $pltyp * 50);
        $tritiumprod = rand(5, 50 + $pltyp * 25);


        $quer = "INSERT INTO `planets` (`pid`, `name`, `typeid` , `coordx`, `coordy`, `solsysx`, `solsysy`, `goldprod`, `tritiumprod`, `metalumprod`,`lastupdate`) 
			VALUES ($nid, '$plname', $pltyp, $px, $py, $solsysx, $solsysy, $goldprod, $tritiumprod, $metalumprod,$lastupd )";
        $qres = query_exec($quer);
        if (!$qres) {
            die('Error inserting:' . $quer);
        }
        addbuildingstoplanet($nid);
    }
    echo '--->OK <br>';
    flush();
}

function cleargalaxy()
{
    $tblarr = array("planets", "world", "buildings", "fleets", "fleetships", "routes", "routeways", "ships", "shiptypes", "battlefleets",
        "battlefleetships", "battles", "messages", "techuser");

    foreach ($tblarr as $i => $value) {
        $quer = "delete  from $value";
        $qres = query_exec($quer);
        echo $quer . "<BR>";
        if (!$qres) {
            die('Error clearing $value:' . $quer);
        }
    }


    echo 'Galaxy Cleared!!!<br>';
}

function defaultshiptypes($userid)
{

    /*                 (1, 'Corvette I', 1, 1, 1, 1, 1, 1, 0, 1, $userid),
      (2, 'Cruiser I', 2, 1, 2, 1, 2, 0, 0, 1, $userid),
      (3, 'Destroyer I', 2, 2, 3, 2, 1, 1, 2, 2, $userid),
      (4, 'Battleship I', 2, 2, 4, 2, 1, 2, 2, 2, $userid),
      (5, 'Frigate I', 1, 1, 5, 1, 1, 0, 0, 1, $userid), */


    $quer = "INSERT INTO `shiptypes` (`stid`, `stypename`, `propulsionid`, `computerid`, `hullid`, `sensorid`, `weapon1id`, `weapon2id`, `weapon3id`, `shieldid`, `ownerid`) VALUES
                 (null, 'Interceptor', 0, 0, 6, 0, 0, 0, 0, 0, $userid),                 
                 (null, 'Corvette'   , 0, 0, 1, 0, 0, 0, 0, 0, $userid)
                ";

    $qres = query_exec($quer);
    if (!$qres) {
        die('Error inserting shiptypes:' . $quer);
    }
}

function startupusers()
{
    $quer = "UPDATE `users` set `techpoints`=0, `techlastupdate`= NULL, `pid`= NULL";
    $qres = query_exec($quer);
    if (!$qres) {
        die('Error updating users:' . $quer);
    }

    $quer = "SELECT * from `users`;";
    $qres = query_exec($quer);
    if (!$qres) {
        die('Error getting users:' . $quer);
    }


    while ($row = query_fetch_array($qres)) {
        echo $row["id"] . ") ";
        echo "New planet for user id: " . $row['name'] . "<BR>";
        echo newuser($row["id"]) . " <BR>";
    }
}

function creategalaxymap($solsysno, $solsize, $quadrpersys)
{
    global $galaxysize, $solsyssize, $maxplanets, $solsyscover;

    $galaxysize = $solsysno; //size of galaxy x and y
    $solsyssize = $solsize; //quadrants per solar system
    $solsyscover = $quadrpersys;
    $maxplanets = round((($solsyssize * $solsyssize) * $solsyscover) / 100); //how many planets per solar system

    echo '<br>Galaxy size = ' . $galaxysize . ' x ' . $galaxysize . ' <br>
		Quadrants per Solar system =  ' . $solsyssize . ' x ' . $solsyssize . '<br>
 		Planets per Solar system = ' . $maxplanets . ' <br>
  
		<br><br>';
    echo "Clearing Galaxy...<BR>";
    cleargalaxy();
    echo "Creating New Galaxy<BR>";

    $solcx = 0;
    while ($solcx < $galaxysize) {
        $solcy = 0;
        while ($solcy < $galaxysize) {
            fillsolsys($solcx, $solcy);
            $solcy++;
        }
        $solcx++;
    }
    //add new world -- always one in db
    $wid = gettablenextid('world', 'wid');
    $quer = "INSERT INTO `world` (`wid`,`worldname`,`galaxysize`,`solsyssize`,`solsyscover`, `maxplanets`) values($wid,'wname',$galaxysize,$solsyssize,$solsyscover,$maxplanets)";
    $qres = query_exec($quer);
    if (!$qres) {
        die('Error inserting:' . $quer);
    }

    //set register users to some planet
    startupusers();
}
