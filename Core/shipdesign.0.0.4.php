
<?php

include_once("gamesession.php");
include_once("myutils.php");
include_once "mytabset.php";
include_once("shipdesignutils.php");

includecorepage("shipbuild");
include_once("srvendturn.php");



global $pgcontent, $pgcontentstyle, $img;

function showshipimage(&$imgcontent, &$imgcontentstyle)
{
    $imgname = $_SESSION['shipimg'];
    adddebugval('imgname', $imgname);
    //	  $imgcontent.="<img src='Images/upgradepos.png' class='upgimg'>";
    $imgcontent .= "<img src='Images/" . $imgname . "' class='shipimg' id='shipimg'>";

    $imgcontentstyle .= '
	   ';
}

function getlboxoption($val, $valname, $sel = false)
{
    if ($sel) {
        $s = " selected";
    } else {
        $s = "";
    }
    return '<option  value="' . $val . '"' . $s . '>' . $valname . '</option>';
}

function getpulldown($upgspec, $caption, $upgid, $qres, $qrcnt, $fldupgid, $fldupgname, $left, $top, &$selarr, &$tabcontentstyle, &$ajaxcode)
{
    $isajax = $_SESSION['isajax'];

    $label = 'lbl' . $upgspec;
    $upgidsel = $upgspec . 'sel';


    $cont = " <div class='div$upgidsel divupgsel addons'>
					<label class='lbl$upgidsel lblupgsel'>$caption</label>
					<select class='addonslbox $upgidsel upgsel' name='$upgidsel' 	
					 	  onchange='" . $upgidsel . "changed($upgidsel,this.selectedIndex);'>							
			";
    $currow = array();
    $cont .= getlboxoption(0, 'Nothing', false);
    for ($i = 1; $i < $qrcnt + 1; $i++) {
        $xarr = query_fetch_array($qres);
        $vi = $xarr[$fldupgid];
        $v = $xarr[$fldupgname];
        if ($vi == $upgid) {
            $sel = true;
            $currow = $xarr;
        } else {
            $sel = false;
        }
        $cont .= getlboxoption($vi, $v, $sel);
    }
    $cont .= "
				</select>
				
			";

    //return back

    $selarr = $currow;


    $jsfunc = '
			
				si=selindex+1;
				//alert(upgsel.name+" "+si);				
                                ln=upgsel.name.length;
				upg=upgsel.name.substring(0,ln-3);
					
				if (si==1) {
				  sizeval="";
				  goldval="";
				  metalumval="";
				  tritiumval="";
				  armorval="";
				  powerval="";
				  distanceval="";
				  speedval="";
				  damageval="";
				  accuracyval="";
				  newupg=upg;
				  
				  
				}
				else {
				  upgobj=getElementByName(upgsel.name);
				  upgval=upgobj.options[upgobj.selectedIndex].value;
				  varsend="upg="+upg+"&id="+upgval;
				  remurl="Core/getupginfo.php";
				  data=getAjaxData(remurl,varsend);
				  var obj = jQuery.parseJSON(data);
				  
				  newupg=obj.upg;	

				  sizeval=obj.size;	
				  goldval=obj.ngold;	
				  metalumval=obj.nmetalum;	
				  tritiumval=obj.ntritium;	
				  switch (newupg)
				  {
				    case "hull":  armorval=obj.armor;
						  powerobj=getElementByName("propsellblpower");
						  powerval=powerobj.innerHTML;	
						
						  varsend="speed=1&size="+sizeval+"&power="+powerval;
						  remurl="Core/getupginfo.php";
				  		  data=getAjaxData(remurl,varsend);
				  		  var obj2 = jQuery.parseJSON(data);
						  speedval=obj2.speed;								
                                                  //todo: get from ajax the ship hull image name
                                                  $("#shipimg").attr("src", "Images/frigate.png");
                                                  
						  break;
		   			case "comp": accuracyval=obj.accuracy;break;
		 			case "prop": powerval=obj.power;
					             hullszobj=getElementByName("hullsellblsize");
								 maxsizeval=hullszobj.innerHTML;
								 
									varsend="speed=1&size="+maxsizeval+"&power="+powerval;
									remurl="Core/getupginfo.php";
				  					data=getAjaxData(remurl,varsend);
				  					var obj2 = jQuery.parseJSON(data);
								 	speedval=obj2.speed;
					 			 break;
		  			case "sensor":distanceval=obj.distance;break;
		  			case "shield":powerval=obj.power;break;
		  			case "weapon1":					
 		  			case "weapon2":
  		  			case "weapon3":distanceval=obj.weapondist;damageval=obj.weapondmg;break;
					 
				  }
				  
				  
				}
				
				
				changeLabelText(upgsel.name+"lblsize",sizeval);
				changeLabelText(upgsel.name+"lblgold",goldval);
				changeLabelText(upgsel.name+"lblmetalum",metalumval);
				changeLabelText(upgsel.name+"lbltritium",tritiumval);
				
				
				switch (newupg)
				{
				  case "hull": changeLabelText(upgsel.name+"lblarmor",armorval); 
				  	       changeLabelText("propsellblspeed",speedval); break;	
					
		   			case "comp": changeLabelText(upgsel.name+"lblaccuracy",accuracyval);  break;	
		 			case "prop": changeLabelText(upgsel.name+"lblpower",powerval);  
                                                     changeLabelText(upgsel.name+"lblspeed",speedval);   break;
		  			case "sensor":changeLabelText(upgsel.name+"lbldistance",distanceval);  break;	
		  			case "shield":changeLabelText(upgsel.name+"lblpower",powerval);  break;	
		  			case "weapon1":		
 		  			case "weapon2": 
  		  			case "weapon3": changeLabelText(upgsel.name+"lblwdist",distanceval);  
  					                changeLabelText(upgsel.name+"lblwdamg",damageval);  break;
					
				}
				
				recalcinfo();
							
			';
    if (!$isajax) {
        addjsfunction($upgidsel . 'changed', $jsfunc, 'upgsel,selindex');
    } else {
        $ajaxcode .= getajaxjsfunction($upgidsel . 'changed', $jsfunc, 'upgsel,selindex');
    }



    $jsfunc = '

						maxsize=toNumber(getElementValByName("hullsellblsize"));
											
						size=toNumber(getElementValByName("propsellblsize"));
						size+=toNumber(getElementValByName("compsellblsize"));
						size+=toNumber(getElementValByName("sensorsellblsize"));
						size+=toNumber(getElementValByName("shieldsellblsize"));
						size+=toNumber(getElementValByName("weapon1sellblsize"));
						size+=toNumber(getElementValByName("weapon2sellblsize"));
						size+=toNumber(getElementValByName("weapon3sellblsize"));

						gold=toNumber(getElementValByName("propsellblgold"));
						gold+=toNumber(getElementValByName("hullsellblgold"));						
						gold+=toNumber(getElementValByName("compsellblgold"));
						gold+=toNumber(getElementValByName("sensorsellblgold"));
						gold+=toNumber(getElementValByName("shieldsellblgold"));
						gold+=toNumber(getElementValByName("weapon1sellblgold"));
						gold+=toNumber(getElementValByName("weapon2sellblgold"));
						gold+=toNumber(getElementValByName("weapon3sellblgold"));

						metalum=toNumber(getElementValByName("propsellblmetalum"));
						metalum+=toNumber(getElementValByName("hullsellblmetalum"));						
						metalum+=toNumber(getElementValByName("compsellblmetalum"));
						metalum+=toNumber(getElementValByName("sensorsellblmetalum"));
						metalum+=toNumber(getElementValByName("shieldsellblmetalum"));
						metalum+=toNumber(getElementValByName("weapon1sellblmetalum"));
						metalum+=toNumber(getElementValByName("weapon2sellblmetalum"));
						metalum+=toNumber(getElementValByName("weapon3sellblmetalum"));

						tritium=toNumber(getElementValByName("propsellbltritium"));
						tritium+=toNumber(getElementValByName("hullsellbltritium"));						
						tritium+=toNumber(getElementValByName("compsellbltritium"));
						tritium+=toNumber(getElementValByName("sensorsellbltritium"));
						tritium+=toNumber(getElementValByName("shieldsellbltritium"));
						tritium+=toNumber(getElementValByName("weapon1sellbltritium"));
						tritium+=toNumber(getElementValByName("weapon2sellbltritium"));
						tritium+=toNumber(getElementValByName("weapon3sellbltritium"));

						
						setElementValByName("size",size);
						setElementValByName("maxsize",maxsize);
						setElementValByName("gold",gold);
						setElementValByName("metalum",metalum);
						setElementValByName("tritium",tritium);

						sizeobj=getElementByName("size");
						if (size>maxsize){						
							sizeobj.style.backgroundColor = "red";							
						}
						else
						  sizeobj.style.backgroundColor = "green";
			   
			   ';
    if (!$isajax) {
        addjsfunction('recalcinfo', $jsfunc, '');
    } else {
        $ajaxcode .= getajaxjsfunction('recalcinfo', $jsfunc, '');
    }

    $contstl = "
				.div$upgidsel
				{
				   left:$left;
				   top:$top;
				}
				.lbl$upgidsel
				{
				}
				.$upgidsel
				{
				}				
			";

    if (!$isajax) {
        addstyle($contstl);
    } else {
        $tabcontentstyle .= $contstl;
    }


    return $cont;
}

function getupginfo($currow, $upg)
{
    if (!isset($currow['size'])) {
        $currow['size'] = '';
    }
    if (!isset($currow['ngold'])) {
        $currow['ngold'] = 0;
    }
    if (!isset($currow['nmetalum'])) {
        $currow['nmetalum'] = 0;
    }
    if (!isset($currow['ntritium'])) {
        $currow['ntritium'] = 0;
    }
    $size = getformatednumber($currow['size']);
    $gold = getformatednumber($currow['ngold']);
    $metalum = getformatednumber($currow['nmetalum']);
    $tritium = getformatednumber($currow['ntritium']);

    $cont = "
				<div class='otherinfo'>
				<label>Size:</label>
				<label name='" . $upg . "sellblsize'>$size</label><br>
				<label>Gold:</label>
				<label name='" . $upg . "sellblgold'>$gold</label><br>
				<label>Metalum:</label>
				<label name='" . $upg . "sellblmetalum'>$metalum</label><br>
				<label>Tritium:</label>
				<label name='" . $upg . "sellbltritium'>$tritium</label><br>
				<br>
			";

    adddebugval('upg', $upg);
    switch ($upg) {
        case 'hull':
            $cont .= "
  				   <label>Armor:</label>
				   <label name='" . $upg . "sellblarmor'>" . $currow['armor'] . "</label><br>
			  			";
            break;
        case 'prop':

            $speed = calculatespeed($currow['power'], $_SESSION['maxsize']);

            $cont .= "
  				   <label>Power:</label>
				   <label name='" . $upg . "sellblpower'>" . $currow['power'] . "</label><br>
  				   <label>Speed:</label>
				   <label name='" . $upg . "sellblspeed'>" . $speed . "</label><br>
				   
			  			";

            break;
        case 'comp':
            $cont .= "
  				   <label>Accuracy:</label>
				   <label name='" . $upg . "sellblaccuracy'>" . $currow['accuracy'] . "</label><br>
			  			";

            break;
        case 'sensor':
            $cont .= "
  				   <label>Distance:</label>
				   <label name='" . $upg . "sellbldistance'>" . $currow['distance'] . "</label><br>
			  			";

            break;
        case 'shield':
            $cont .= "
  				   <label>Power:</label>
				   <label name='" . $upg . "sellblpower'>" . $currow['power'] . "</label><br>
			  			";

            break;

        case 'weapon1':
        case 'weapon2':
        case 'weapon3':
            if (!isset($currow['weapondist'])) {
                $currow['weapondist'] = 0;
            }
            if (!isset($currow['weapondmg'])) {
                $currow['weapondmg'] = 0;
            }
            $cont .= "
  				   <label>Distance:</label>
				   <label name='" . $upg . "sellblwdist'>" . $currow['weapondist'] . "</label><br>
  				   <label>Damage:</label>
				   <label name='" . $upg . "sellblwdamg'>" . $currow['weapondmg'] . "</label><br>				   
			  			";
            break;
    }
    $cont .= "	
				</div>
				";


    return $cont;
}

function getshiptypeinfo(&$tabcontent, &$tabcontentstyle, $shiptpid, &$ajaxcode)
{
    $uid = $_SESSION["id"];
    adddebugval('shiptype id', $shiptpid);
    $totsize = 0;
    $totgold = 0;
    $totmetalum = 0;
    $tottritium = 0;
    $left = 00;
    $top = 0;

    //hull
    $qres = getSelectedShipHull($shiptpid);
    if ($qres != null) {
        $dbarr = query_fetch_array($qres);
        $qres = getAllShipHullsforUser($uid, $qrcnt);
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'hull',
                'HULLS',
                $dbarr['hullid'],
                $qres,
                $qrcnt,
                    'xhullid',
                'hullname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );
            $tabcontent .= getupginfo($sel, 'hull');
            $tabcontent .= '</div>';
            $_SESSION['shipimg'] = $sel['image'];
            $_SESSION['maxsize'] = $sel['size'];
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }
        //computer
        $left = 00;
        $top = 230;
        $qres = getAllShipComputersforUser($uid, $qrcnt);
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'comp',
                'COMPUTERS',
                $dbarr['computerid'],
                $qres,
                $qrcnt,
                    'xcompid',
                'compname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'comp');
            $tabcontent .= '</div>';
            if (myisset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }

        //propulsion

        $left = 190;
        $top = 0;
        $qres = getAllShipPropulsionsforUser($uid, $qrcnt);
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'prop',
                'PROPULSION',
                $dbarr['propulsionid'],
                $qres,
                $qrcnt,
                    'xpropid',
                'propname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'prop');
            $tabcontent .= '</div>';
            if (myisset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }

        //sensors
        $left = 190;
        $top = 230;
        $qres = getAllShipSensorsforUser($uid, $qrcnt);
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'sensor',
                'SENSORS',
                $dbarr['sensorid'],
                $qres,
                $qrcnt,
                    'xsensid',
                'sensname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );
            $tabcontent .= getupginfo($sel, 'sensor');
            $tabcontent .= '</div>';
            if (myisset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }

        //shields
        $left = 380;
        $top = 230;
        $qres = getAllShipShieldsforUser($uid, $qrcnt);
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'shield',
                'SHIELDS',
                $dbarr['shieldid'],
                $qres,
                $qrcnt,
                    'xshieldid',
                'shieldname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'shield');
            $tabcontent .= '</div>';
            if (myisset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }


        //Weapons
        //$quer="SELECT * FROM `x_weapons` order by `size`";
        //$quer="SELECT * from x_weapons where concat('XW',xweaponid) in
        //         (select xtraid as xid from `techuser`,`technology` where techuser.techid=technology.techid  and userid=$uid) order by `size`";
        $qres = getAllShipWeaponsforUser($uid, $qrcnt);

        $left = 380;
        $top = 0;
        if ($qres != null) {
            $tabcontent .= getpulldown(
                'weapon1',
                'WEAPON 1',
                $dbarr['weapon1id'],
                $qres,
                $qrcnt,
                    'xweaponid',
                'weaponname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'weapon1');
            $tabcontent .= '</div>';
            if (myisset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (myisset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (myisset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (myisset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }

        //	$quer="SELECT * FROM `x_weapons` order by `size`";

        $left = 380 + 190;
        $top = 0;
        if ($qres != null) {
            $qres->data_seek(0);
            $tabcontent .= getpulldown(
                'weapon2',
                'WEAPON 2',
                $dbarr['weapon2id'],
                $qres,
                $qrcnt,
                    'xweaponid',
                'weaponname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'weapon2');
            $tabcontent .= '</div>';
            if (isset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (isset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (isset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (isset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }

        //	$quer="SELECT * FROM `x_weapons` order by `size`";
        $left = 380 + 190;
        $top = 230;
        if ($qres != null) {
            $qres->data_seek(0);
            $tabcontent .= getpulldown(
                'weapon3',
                'WEAPON 3',
                $dbarr['weapon3id'],
                $qres,
                $qrcnt,
                    'xweaponid',
                'weaponname',
                $left,
                $top,
                $sel,
                $tabcontentstyle,
                $ajaxcode
            );

            $tabcontent .= getupginfo($sel, 'weapon3');
            $tabcontent .= '</div>';
            if (isset($sel['size'])) {
                $totsize += $sel['size'];
            }
            if (isset($sel['ngold'])) {
                $totgold += $sel['ngold'];
            }
            if (isset($sel['nmetalum'])) {
                $totmetalum += $sel['nmetalum'];
            }
            if (isset($sel['ntritium'])) {
                $tottritium += $sel['ntritium'];
            }
        }


        $tabcontent .= "<div >";

        $tabcontent .= "</div>";

        $_SESSION['totsize'] = $totsize;
        $_SESSION['totgold'] = $totgold;
        $_SESSION['totmetalum'] = $totmetalum;
        $_SESSION['tottritium'] = $tottritium;
    }
}

function getshipbuild2(&$tabcontent, &$tabcontentstyle, &$ajaxcode)
{
    $userid = $_SESSION['id'];
    $selindex = $_SESSION['selindex'];

    $isajax = $_SESSION['isajax'];

    $quer = "SELECT * FROM `shiptypes`,`x_hulls` where (ownerid=$userid) and (hullid=xhullid) order by `size`";

    adddebugval('shp build2 selidx', $selindex);

    if (executequery($quer, $qres, $qrcnt)) {
        $tabcontent .= "	
			  <div class='shipsel'> 	
			   <form method='post' name='delship' id='delship' value='submit'>		 
				<select name='shiptype' id='shiptype' class='shiptype' SIZE=11 style='' 
				     onchange='newimage(this.selectedIndex);' >					 			  		 
			";
        if (!isset($shipsel)) {
            if (myisset(getsessionvar('shipsel'))) {
                $shipsel = $_SESSION['shipsel'];
            } else {
                $shipsel = null;
            }
        }
        if ($shipsel == null and $selindex == null) {
            $selindex = 1;
            $_SESSION['selindex'] = $selindex;
        }

        $selid = -1;
        for ($i = 1; $i < $qrcnt + 1; $i++) {
            $dbarr = query_fetch_array($qres);
            $v = $dbarr['stypename'];
            $vi = $dbarr['stid'];
            adddebugval("stypename", $v);
            adddebugval("stid", $vi);
            if (($selindex != null) and ($selindex == $i)) {
                $shipsel = $vi;
            }

            if ($vi == $shipsel) {
                $_SESSION['shipimg'] = $dbarr['image'];
                $selid = $vi;
            }
            $tabcontent .= getlboxoption($vi, $v, $vi == $shipsel);
        }
        $tabcontent .= "</select>                                                       
			<input type='submit' class='mybutt2' value='Delete ship'  />
		    <input type='hidden' name='submit' value='submit2' />
			</form>
			 </div>";
        showshipimage($tabcontent, $tabcontentstyle);
        $tabcontent .= "
				<form method='post' name='newship' id='newship' value='submit'>
				<div class='shipupgs'>
				";

        getshiptypeinfo($tabcontent, $tabcontentstyle, $selid, $ajaxcode);

        $tabcontent .= "
				</div>
			";


        $totsize = getformatednumber($_SESSION['totsize']);
        $maxsize = getformatednumber($_SESSION['maxsize']);
        $totgold = getformatednumber($_SESSION['totgold']);
        $totmetalum = getformatednumber($_SESSION['totmetalum']);
        $tottritium = getformatednumber($_SESSION['tottritium']);

        $tabcontent .= "
				<div class='newship'>
				<table width='600' border='0' cellspacing='1' cellpadding='0'>
  				
  				 <tr>
    				<td align='center'><input type='text' name='shipname' id='shipname' value='New Ship' style='position:relative;left:6px;width:146px;'/></td>
				<td align='center'><label name='maxsize' class='labelno' style='position:relative;left:18px;width:84px;' />$maxsize</td>
    				<td align='center'><label name='size' class='labelno' style='position:relative;left:30px;width:84px;'/>$totsize</td>
    				<td align='center'><label name='gold' class='labelno' style='position:relative;left:42px;width:112px;'/>$totgold</td>
    				<td align='center'><label name='metalum' class='labelno' style='position:relative;left:54px;width:112px;'/> $totmetalum</td>
				    <td align='center'><label name='tritium' class='labelno' style='position:relative;left:66px;width:112px;'/>$tottritium</td>
				 </tr>
				</table>
				</div>
				<div style='float:left'>		
  			  <input type='submit' class='mybutt' value='Create new ship'  />
			  <input type='hidden' name='submit' value='submit' />
			  </div>
			  </form>
			  
			";

        $jscr = "		
							
				$(function() {
				    $( 'input[type=submit]' )
      				.button()
      				.click(function( event ) {
        				
      					});
  				});
			";

        if (!$isajax) {
            addjscript($jscr);
        } else {
            $ajaxcode .= $jscr;
        }


        $_SESSION['shipsel'] = $shipsel;

        $jsfunc = " 
			
				si=selindex+1;
				
				if (window.location.href.indexOf('selindex')>0) {
  				   s=getMainURL()+'?'+changeQueryVariable('selindex',si);
				   window.location.href=s;
				}
				else
                                 window.location.href=window.location.href+'&selindex='+si;
			";

        $jsfunc = " 
                                  si=selindex+1;
                                  setAjaxSessionParam('selindex',si);
                                  tabpressed('',0);
                                  document.getElementById('shiptype').focus();
                                ";
        if (!$isajax) {
            addjsfunction('newimage', $jsfunc, 'selindex');
        } else {
            $ajaxcode .= getajaxjsfunction('newimage', $jsfunc, 'selindex');
        }


        $incjsf = getjsincludefile('jscript/common.js');
        addincludefile($incjsf);


        $tabcontentstyle .= "
			";
    }
    return $qrcnt > 0;
}

function showshipdesign()
{

    //test tabs
    $tabarr[0] = 'Ship Design';
    $maintab = "shipdsnmain";


    $tabcontent = "<div class='$maintab' name='$maintab'> </div>";
    $tabcontentstyle = "
		";

    $tp = 130;
    $hgt = 1024 - $tp - 100 - 150; //150 is top menu //100 is tab top
    createtab3($maintab, $tabarr, 10, $tp, 1000, $hgt, $tabcontent, 'tabpressed', 180);

    addoutput("", $tabcontentstyle);

    //tab pressed so get data through ajax
    $jscript = "
		
			
		
		   function tabpressed(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			    info=0;
			 break;
			 
			}

			   obj=getAjaxInfo(info,'shipdesign','p=0','myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
			  		  
			   if (obj.scriptcode!='')
  			     addScriptToPage(obj.scriptcode,'myscript');
			   

			 // call a default function if exists
				if (typeof initform == 'function') { 
				  initform(); 
				}//	else alert('no func');		 
		   }
		
		";

    addjscript($jscript);

    $incjsf = getjsincludefile('jscript/common.js');
    addincludefile($incjsf);
}

function getshipdesign(&$tabcontent, &$tabcontentstyle, &$ajaxcode)
{
    $tabcontent .= "<div class='shipdsgn'>";
    getshipbuild2($tabcontent, $tabcontentstyle, $ajaxcode);
    $tabcontent .= "</div>";
    $tabcontentstyle .= "";
}

//Main ajax function
function shipdesigninforequested($info)
{
    $selindex = $_SESSION['selindex'];

    adddebugval('AJAX sidx', $selindex);

    db_connect();
    activityoccur();
    //$selplanet=$_SESSION['selplanet'];
    $ajaxcode = '';
    $tabcontent = '';
    $tabcontentstyle = '';
    switch ($info) {

        case 0://Ship Design requested
            getshipdesign($tabcontent, $tabcontentstyle, $ajaxcode);
            $sntback = $tabcontent . "<style type='text/css'>" . $tabcontentstyle . "</style>";

            $retarr['content'] = $sntback;
            break;
    }

    $retarr['scriptcode'] = $ajaxcode;
    getdebugdata($retarr);

    echo json_encode($retarr);
}

function init_page()
{
    getsessionvars();

    if (myisset(filter_input(INPUT_GET, 'selindex'))) {
        $selindex = filter_input(INPUT_GET, 'selindex');
        adddebugval('sidx', $selindex);
    } else {
        $selindex = null;
    }
    $_SESSION['selindex'] = $selindex;

    if (myisset(filter_input(INPUT_POST, 'submit'))) {
        if (filter_input(INPUT_POST, 'submit') == 'submit') {
            $selindex = null;
            $_SESSION['selindex'] = $selindex;
            //  adddebugval('building new ship');
            buildnewship();
        }



        if (filter_input(INPUT_POST, 'submit') == 'submit2') {
            $shipid = filter_input(INPUT_POST, 'shiptype');
            adddebug("Delete $shipid shiptype<br>");
            deleteship($shipid);
        }
    }
    //  $cont = '<div name="shipdesign" class="shipdesign">';
    $cont = '';
    $contstyle = '
	  
	  ';
    adddebugval('si', $selindex);
    addoutput($cont, $contstyle);
    showshipdesign();

    //$cont = '</div>';
    //addoutput($cont, $contstyle);
}

// Always run when included

if (myisset(filter_input(INPUT_GET, 'info'))) {
    //$_SESSION['isajax']=true;
    shipdesigninforequested(filter_input(INPUT_GET, 'info'));
    adddebugval('INFO', filter_input(INPUT_GET, 'info'));
    //$_SESSION['isajax']=false;
} else {
    $_SESSION['isajax'] = false;
}
$selplanet = $_SESSION['selplanet'];
if (myisset($selplanet)) {
    adddebug("Planet $selplanet selected<br>");

    doendturn($selplanet);

    //checkbuttons();
} else {
    doendturn();
}
?>