<?php

include_once("gamesession.php");
include_once("economy.php");
include_once("myutils.php");
include_once("shiputils.php");
include_once("planetutils.php");

global $plg, $plm, $plt;

function getshipsforplanet(&$tabcontent, &$tabcontentstyle, $pid, &$ajaxcode)
{
    $userid = $_SESSION['id'];
    getplanetowner($pid, $pluserid, $plusername);
    if ($pluserid != $userid) {
        $tabcontent = "You dont own that planet $pluserid != $userid";
        return false;
    }


    $quer = "
	SELECT * FROM `shiptypes`, `x_hulls`  where ownerid=$userid and `hullid`=`xhullid` order by size;
		";

    if (executequery($quer, $qres, $qrcnt) and $qrcnt > 0) {
        calculateproduction($pid, $plg, $plm, $plt);

        $tabcontent .= "<div class='planshiptit'></div>"
                . "<div class='planship planshipscroll'>";


        //			   LEFT JOIN x_weapons xw1	ON `weapon1id`=xw1.`xweaponid`
        //			   LEFT JOIN x_weapons xw2	ON `weapon2id`=xw2.`xweaponid`
        //			   LEFT JOIN x_weapons xw3	ON `weapon3id`=xw3.`xweaponid`
        //			   JOIN x_propulsions ON `propulsionid`=`xpropid`
        //			   JOIN `x_computers` ON `computerid`=`xcompid`
        //			   JOIN `x_hulls` ON `hullid`=`xhullid`
        //			   JOIN `x_sensors` ON `sensorid`=`xsensid`
        //			   JOIN `x_shields` ON `shieldid`=`xshieldid`


        for ($i = 1; $i < $qrcnt + 1; $i++) {
            $dbarr = query_fetch_array($qres);
            $dbarr = getshipinfo($dbarr);
            $img = $dbarr['image'];
            $imgsml = 'Images/' . getsmallimage($img);

            $tp = 1 + ($i - 1) * 110;
            $sname = $dbarr['stypename'];
            $g = $dbarr['ngold'];
            $m = $dbarr['nmetalum'];
            $t = $dbarr['ntritium'];

            $frmnm = 'frm_' . $sname;
            $maxships = calculatemaxships($plg, $plm, $plt, $g, $m, $t);
            $defval = floor($maxships / 3);
            $size = $dbarr['size'];
            $maxsize = $dbarr['maxsize'];
            $armor = $dbarr['armor'];
            $speed = calculatespeed($dbarr['engpower'], $dbarr['maxsize']);
            $scan = $dbarr['sensdist'];
            $stid = $dbarr['stid'];
            $divclass = "pbs_$stid";

            $tm = calculatebuildtimeforship($stid);
            $tm1 = getshiptime($tm, $mn, $ds);
            $tmls = "$mn m, $ds d";

            $tabcontent .= "<div class='planshipbuilt $divclass'>
                                            <div class='pbsimg'>
                                                <img src='$imgsml' title=$sname />"
                    . "</div>"
                    . "
					   <div class='pbsstitle'> $sname </div>
                                           <label class='pbsarmor'> $armor </label>
                                           <label class='pbsspeed'> $speed </label>
                                           <label class='pbsscan'> $scan </label>					    
                                           <label class='pbsgold'> $g </label>
                                           <label class='pbsmetal'> $m </label>
                                           <label class='pbstrit'> $t </label>					    
                                           <label class='pbstimes'> $tmls </label>
                                           <label class='pbstime'> $tm1 </label>
					  <form name='$frmnm' method='post'>
  					  
					  <input type='number' name='shipid' value=$stid hidden/>
					  <input class='pbsquant' type='number' name='quant' min='0' max='$maxships' value=$defval />
					  <label class='pbsquantmax'>$maxships</label>
					  <input type = 'hidden' name = 'submit' value = 'submit2'>
					  
  					  <div class='pbsbutton'>
					    	<input type='image' name='submit' value='submit2' 
								src='Images/Planet_Build_But.png' border='0' 
			 					onmouseover='" . 'this.src="Images/Planet_Build_But_ovr.png"' . "'
								onmouseout='" . 'this.src="Images/Planet_Build_But.png"' . "'
								style='top:10;position:relative'
							/>

					  </div>
					  </form>
				   </div>
				   ";

            $tabcontentstyle .= ".$divclass{top:$tp}";
        }
        $tabcontent .= "</div>";
    }



    $tabcontentstyle .= " ";

    $isajax = $_SESSION['isajax'];
    if (!$isajax) {
        addjsfunction('initform', "");
    } else {
        $ajaxcode .= getajaxjsfunction('initform', '//alert("hi3");');
    }
}

function getnewstarttimefromshipqueue($pid)
{
    $qr = "select * from (SELECT *,(sttime+durtime) as entime FROM `shipbuild`) as a where pid=$pid order by a.entime desc limit 1";
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $dbarr = query_fetch_array($qrres);
        return $dbarr['entime'];
    } else {
        return mtimetn();
    }
}

function startbuildingship($pid, $plg, $plm, $plt, $stid, $quant)
{
    adddebug("start building ship");

    $userid = $_SESSION['id'];

    $quer = "SELECT * FROM `shiptypes`  where ownerid=$userid and `stid`=$stid";

    if (executequery($quer, $qres, $qrcnt)) {
        $dbarr = query_fetch_array($qres);
        $dbarr = getshipinfo($dbarr);
        $g = $dbarr['ngold'];
        $m = $dbarr['nmetalum'];
        $t = $dbarr['ntritium'];
        $maxships = calculatemaxships($plg, $plm, $plt, $g, $m, $t);
        if ($quant <= $maxships) {
            //start building the ship
            //echo 'building' ;
            planetaddresources($pid, -$g * $quant, -$m * $quant, -$t * $quant);
            //fill the build queue
            //	$tmst=mtimetn();
            $tmst = getnewstarttimefromshipqueue($pid);
            $spdur = calculatebuildtimeforship($stid);
            $tmdur = mulshiptime($spdur, $quant);
            adddebugval('duration', $tmdur);
            $sbid = gettablenextid('shipbuild', 'sbid');
            $qur = "INSERT INTO `shipbuild`
					    (sbid,stid,quantity,sttime,durtime,shiptime,pid)
					   VALUES ($sbid,$stid,$quant,$tmst,$tmdur,$spdur,$pid)";
            query_exec($qur);
        } else {
            //return error message
            adddebug("too many ships wanted:$quant > max:$maxship");
            $t = $_SERVER['QUERY_STRING'];
            header("Location: index.php?$t&msg='not enough resources!!!' ");
        }
    }
}
