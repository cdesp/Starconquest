<?php

//	includecorepage("techutils");

function getalltech(&$qrcnt, $playerid)
{
    $quer = "SELECT * FROM `technology`,`techcat` where tcatid=tcatdid";
    addbottomoutput($quer . "<BR>");
    adddebug($quer . "<BR>");
    if (executequery($quer, $qres, $qrcnt)) {
        return $qres;
    } else {
        $qrcnt = 0;
        return false;
    }
}

function gettech(&$qrcnt, $catid, $prelevel = null, $playerid = null)
{
    $userid = $_SESSION['id'];

    if ($playerid == null) {
        $playerid = $userid;
    }
    if ($prelevel == null) {
        //$quer = "SELECT * FROM `technology`,`techcat` where tcatid=tcatdid and tcatid=$catid";
        //			$quer = "SELECT `technology`.techid,techname,tcatid,previd,techpoints,techtimedays,techtimehours,effcomment,buildtime
        //			         FROM `technology` join `techcat` on tcatid=tcatdid left join `techuser` on  `technology`.`techid`=`techuser`.`TechID`
        //			         where   tcatid=$catid and (userid=$playerid or isnull(userid))";
        $quer = "SELECT `technology`.techid,techname,tcatid,previd,techpoints,techtimedays,techtimehours,effcomment,buildtime,userid 
                                FROM `technology` join `techcat` on tcatid=tcatdid left join (select * from techuser where userid=$playerid) as `tuser` on `technology`.`techid`=`tuser`.`TechID` 
                                WHERE tcatid=$catid order by techid";
    } else {
        //  		$quer = "SELECT `technology`.techid,techname,tcatid,previd,techpoints,techtimedays,techtimehours,effcomment,buildtime
        //			         FROM `technology` join `techcat` on tcatid=tcatdid left join `techuser` on  `technology`.`techid`=`techuser`.`TechID`
        //where   tcatid=$catid and previd=$prelevel and (userid=$playerid or isnull(userid))";
        $quer = "SELECT `technology`.techid,techname,tcatid,previd,techpoints,techtimedays,techtimehours,effcomment,buildtime,userid 
                                FROM `technology` join `techcat` on tcatid=tcatdid left join (select * from techuser where userid=$playerid) as `tuser` on `technology`.`techid`=`tuser`.`TechID` 
                                WHERE tcatid=$catid and previd=$prelevel order by techid";
    }


    //			$quer = "SELECT * FROM `technology`,`techcat` where tcatid=tcatdid and tcatid=$catid and previd=$prelevel";
    //addbottomoutput($quer."<BR>");
    if (executequery($quer, $qres, $qrcnt)) {
        return $qres;
    } else {
        $qrcnt = 0;
        return false;
    }
}

function gettechcat(&$qrcnt)
{
    $quer = "SELECT * FROM `techcat`";
    if (executequery($quer, $qres, $qrcnt)) {
        return $qres;
    } else {
        $qrcnt = 0;
        return false;
    }
}

function upgradetech($tid)
{
    $userid = $_SESSION['id'];
    $tpneeded = 0;

    adddebug("Tech Discover<BR>");
    //1.get technology to see if there is enough techpoints for user
    //TODO:also we should check if previous tech was discovered
    $quer = "select * from `technology` where techid=$tid";
    if (executequery($quer, $qres, $qrcnt)) {
        $dbarr = query_fetch_array($qres);
        $tpneeded = $dbarr['techpoints'];
        $tpuser = getusertechpoints();
        adddebug("$tpneeded > $tpuser");
        if (tpneeded > tpuser) {
            return "Not enough techpoints";
        } //not enough techpoints
        $timupg = maketimetoupg($dbarr['techtimedays'], $dbarr['techtimehours'], 0); //in secs
        $tim = mtimetn() + $timupg; //also set time to be finished now + $dbarr['techtimedays']+ $dbarr['techtimehours']
    } else {
        return "Invalid technology ID";
    }

    //2. decrease techpoints from user
    $quer = "update users set `techpoints`=`techpoints`-$tpneeded where id=$userid";
    query_exec($quer);
    addbottomoutput($quer);
    adddebug("user points=$tpuser, points reduced=$tpneeded<BR>");
    //3. upgrade
    adddebug("time to discover new tech=$tim<BR>");
    adddebug(getshiptime($tim) . "<BR>");
    $quer = "insert into  `techuser` (techid,userid,buildtime) values($tid,$userid,$tim)";
    if (executequery($quer, $qres, $qrcnt)) {
        return "";
    } else {
        return "Can't insert tech to table!!!";
    }
}

function checktechdiscovered()
{
    $tmnow = mtimetn();
    //adddebugval("time to check",$tmnow);
    $quer = "update `techuser` set buildtime=0 where buildtime>0 and buildtime<=$tmnow  ";
    query_exec($quer);
}
