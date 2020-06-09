<?php

    include_once("gamesession.php");
    include_once("economy.php");
    include_once("myutils.php");
    include_once("shiputils.php");
    
    global $plg,$plm,$plt;
    


    function getplanetfleet(&$tabcontent, &$tabcontentstyle, $pid, &$ajaxcode)
    {
        global $plg,$plm,$plt;
        
        
        $userid=$_SESSION['id'];
        getplanetowner($pid, $pluserid, $plusername);
        if ($pluserid!=$userid) {
            $tabcontent="You dont own that planet $pluserid != $userid";
            return false;
        }
        
        
        getplanetowner($pid, $userid, $username);
        $qres=getshipsonplanet($pid, $userid, $qrcnt);
        
        if ($qres!=false and $qrcnt>0) {
            $tabcontent.="
						<div class='planfleettit'></div>
						<div class='planship'>
						<form name='makefleet' method='post'>
                                                <div class='planfleetscroll'>
						";
        
        
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                $dbarr=getshipinfo($dbarr);
                 
                $img=$dbarr['image'];
                $imgsml='Images/'.getsmallimage($img);
                
                $tp=2+($i-1)*112;
                $sname=$dbarr['stypename'];
                $g=$dbarr['ngold'];
                $m=$dbarr['nmetalum'];
                $t=$dbarr['ntritium'];
                   
                // $frmnm='frm_'.$sname;
                $size=$dbarr['size'];
                $maxsize=$dbarr['maxsize'];
                $armor=$dbarr['armor'];
                $speed=calculatespeed($dbarr['engpower'], $dbarr['maxsize']);
                $scan=$dbarr['sensdist'];
                $stid=$dbarr['stid'];
                $sid=$dbarr['sid'];
                $quant=$dbarr['quantity'];
                $quant=getformatednumber($quant);
                $stq="stq_$sid";
                $divclass="pfc_$sid";
                //				   $tm=calculatebuildtimeforship($stid);
                //				   $tm1=getshiptime($tm);

                   
                $tabcontent.="<div class='planfleetship $divclass' >
                                        <div class='psfimg'>
                                            <img  src='$imgsml' title=$sname />                                           
                                        </div>    
					<div class='psfstitle'> $sname	</div>
                                          <div class='psfsprops'>                                               
                                           <label class='psfarmor'> $armor </Label>
                                           <label class='psfspeed'> $speed </Label>
                                           <label class='psfscan'> $scan </Label>					    
					  </div>					   					  
					  <label class='psfquant'>$quant</label>
					  <input type='number' class='stq' name='$stq' min=0 max=$quant value='0'/>					  
					</div>
				   
				   ";
                  
                $tabcontentstyle.=".$divclass{top:$tp}";
            } //end for i

        
        
            //$tp+=120;
        
            $tabcontent.="  </div>
		  			  <div class='butcreatefleet' >
		  				  <input type = 'hidden' name = 'submit' value = 'submit4'>
					    	<input type='image' name='submit' value='submit4' title='Create the fleet'
								src='Images/ButCreateFleet.png' border='0' 
			 					onmouseover='".'this.src="Images/ButCreateFleet_ovr.png"'."'
								onmouseout='".'this.src="Images/ButCreateFleet.png"'."'
								
							/>

					  </div>                                    
				</form>
			</div>";
        }
        
        $tabcontentstyle.="
		 
		  
		  	  
		 
		 ";


        $isajax=$_SESSION['isajax'];
        if (!$isajax) {
            addjsfunction('initform', "");
        } else {
            $ajaxcode.= getajaxjsfunction('initform', '//alert("hi2");');
        }
    }
        
        //Find a free place near the planet. For now the fleets are on the planet.
    function getfreeplcoords($pid, &$cx, &$cy)
    {
        global $galaxysize,$solsyssize;
        
        $px=0;
        $py=0;
        getcoordsfromplanet($pid, $px, $py);
        $cx=$px;
        $cy=$py;
      
        return true;
      
      
      
        //todo find an empty quadr near the planet to put the fleet
      
        if (getcoordsfromplanet($pid, $px, $py)) {
            for ($ix=-1;$ix<2;$ix++) {
                for ($iy=-1;$iy<2;$iy++) {
                    $x=$px+$ix;
                    $y=$py+$iy;
                    //check boundries
                    $x=max($x, 0);
                    $y=max($y, 0);
                    $x=min($x, $galaxysize*$solsyssize);
                    $y=min($y, $galaxysize*$solsyssize);
                          
                    if (!isquadrfilled($x, $y) and !solsyschange($px, $py, $x, $y)) {
                        $cx=$x;
                        $cy=$y;
                        return true;
                        exit;
                    }
                }
            }
        }
       
        return false;
    }

    function createplanetfleet($pid)
    {
        $userid=$_SESSION['id'];

        //$fltid=gettablenextid('fleets','fltid');
                
        
        getfreeplcoords($pid, $cx, $cy);
        $qr1="insert into `fleets` (`fltid`,`fltname`,`coordx`,`coordy`,`ownerid`) values (null,'$fltname',$cx,$cy,$userid) ";
        query_exec($qr1); //create fleet at cx, cy
        $fltid=query_insert_id();
        $fltname="fleet_$fltid";
        $qr1="update `fleets` set `fltname`='$fltname' where `fltid`=$fltid  ";
        query_exec($qr1); //Update fleet name
        return $fltid;
    }
    
    function addshipstofleet($fltid, $pid, $stid, $quant, $doset=false)
    {
        $userid=$_SESSION['id'];
        //1 add quant of shiptype stid to new fleet item fltid

        $qr2="select * from fleetships where `fltid`=$fltid and `stid`=$stid LIMIT 1";
        if (executequery($qr2, $qres, $qrcnt)) {
            if ($qrcnt>0) {
                adddebug('fleet item found we update<br>');
                $dbarr=query_fetch_array($qres);
                if ($doset==false) {
                    $newq=$dbarr['quantity']+$quant;
                } else {
                    $newq=$quant;
                }
                $qr2="update `fleetships` set `quantity`=$newq where `fltid`=$fltid and `stid`=$stid";
                query_exec($qr2);
            } else {
                adddebug("no fleet item found we create one FLTID=$fltid<br>");
                $qr2="insert into `fleetships` (`fltid`,`stid`,`quantity`) values ($fltid,$stid,$quant)";
                query_exec($qr2);
            }
        }
        
        
        
        //2 remove quant from planet using sid
        if ($pid>0) {
            $quer="update `ships` set `quantity`=`quantity`-$quant where `pid`=$pid and `stid`=$stid and `owner`=$userid";
            query_exec($quer);
        }
    }

    function postdoplanetfleet($pid)
    {
        $userid=$_SESSION['id'];
        $fltid=0;
        $quer = "
			SELECT * FROM `ships`,`shiptypes`,`x_hulls`  where ownerid=$userid and `hullid`=`xhullid` and `pid`=$pid 			and ships.stid=shiptypes.stid order by size;
		" ;

        if (executequery($quer, $qres, $qrcnt)) {
            $docreate=false;
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                $dbarr=getshipinfo($dbarr);
                $sid=$dbarr['sid'];
                $stid=$dbarr['stid'];
                $edtnm='stq_'.$sid;
                if (myisset(filter_input(INPUT_POST, $edtnm))) {
                    $val=filter_input(INPUT_POST, $edtnm);
                    if ($val>0) {
                        if ($docreate==false) {
                            $fltid=createplanetfleet($pid);
                            $docreate=true;
                        }
                        addshipstofleet($fltid, $pid, $stid, $val);
                    }
                } else {
                    $val=0;
                }
                adddebugval($edtnm, $val);
            }
        }
        if (!$docreate) {
            adddebug('<b>no fleet created</b><br>');
        }
    }
