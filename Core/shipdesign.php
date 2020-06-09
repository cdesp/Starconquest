
<?php


   include_once("gamesession.php");
   include_once("myutils.php");
   include_once("shipbuild.php");
   include_once("shipdesignutils.php");

    global $pgcontent,$pgcontentstyle,$img;


    function showshipimage(&$imgcontent, &$imgcontentstyle)
    {
        $imgname=$_SESSION['shipimg'];
        adddebugval('imgname', $imgname);
        //	  $imgcontent.="<img src='Images/upgradepos.png' class='upgimg'>";
        $imgcontent.="<img src='Images/".$imgname."' class='shipimg'>";

        $imgcontentstyle.='
	   .shipimg
	   {
		position:relative;
		left:350px;
		top:25px;   
		width:290px;
		height:180px;   
	   }
	   .upgimg
	   {
		position:absolute;
		left:-10px;
		top:00px;   
		   
	   }	   
	   ';
    }
    




    function getshipbuild(&$tabcontent, &$tabcontentstyle, $shipsel=null)
    {
        $userid=$_SESSION['id'];
        
        $quer="SELECT * FROM `shiptypes` where ownerid=$userid order by `size`";
        
        
        
        if (executequery($quer, $qres, $qrcnt)) {
            $tabcontent.="	
 			   <form method='post' name='newship'>
				<select name='shiptype' class='shiptype' SIZE=11 style='width: 130px;height:223px' 
				     onchange='newimage(this.selectedIndex);' >					 
			";
            if (!myisset($shipsel)) {
                if (myisset($_SESSION['shipsel'])) {
                    $shipsel=$_SESSION['shipsel'];
                } else {
                    $shipsel=1;
                }
            }
            adddebugval('shipsel', $shipsel);
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                $v=$dbarr['stypename'];
                $vi=$dbarr['stid'];
                if ($i==$shipsel) {
                    //				  $_SESSION['shipimg']=$dbarr['image'];
                    $selid=$vi;
                }
                $tabcontent.=getlboxoption($vi, $v, $i==$shipsel);
            }
            $tabcontent.="</select>";
            
            getshiptypeinfo($tabcontent, $tabcontentstyle, $selid);
            $totsize=getformatednumber($_SESSION['totsize']);
            $maxsize=getformatednumber($_SESSION['maxsize']);

            $tabcontent.="
				<div style='float:left'>
				<table width='600' border='0' cellspacing='1' cellpadding='0'>
  				 <tr>
    				<th scope='col'>Name</th>
					<th scope='col'>Max Size</th>
    				<th scope='col'>Size</th>
    				<th scope='col'>Gold</th>
    				<th scope='col'>Metalum</th>
    				<th scope='col'>Tritium</th>
  				 </tr>
  				 <tr>
    				<td align='center'><input type='text' name='name' id='name' value='New Ship' style='width:140 align:center'/></td>
					<td align='center'><input type='text' name='maxsize' id='maxsize' value='$maxsize' style='width:80'/></td>
    				<td align='center'><input type='text' name='size' id='size' value='$totsize' style='width:80'/></td>
    				<td align='center'><input type='text' name='gold' id='gold' value='0' style='width:60'/></td>
    				<td align='center'><input type='text' name='metalum' id='metalum' value='0' style='width:60'/></td>
				    <td align='center'><input type='text' name='tritium' id='tritium' value='0' style='width:60'/></td>
				 </tr>
				</table>
				</div>
				<div style='float:left'>		
  			  <input type='submit' class='mybutt' value='Create new ship'  />
			  <div>
			  </form>
			";
            
            $jscr="		
							
				$(function() {
				    $( 'input[type=submit]' )
      				.button()
      				.click(function( event ) {
        				
      					});
  				});
			";
            
            addjscript($jscr);
            
            
            $_SESSION['shipsel']=$shipsel;
            
            $jsfunc='
			
				si=selindex+1;
				
				if (window.location.href.indexOf("selindex")>0) {
  				   s=getMainURL()+"?"+changeQueryVariable("selindex",si);
				   window.location.href=s;
				}
				else
  				  window.location.href=window.location.href+"&selindex="+si;
			';
            addjsfunction('newimage', $jsfunc, 'selindex');
            
        
            $incjsf=getjsincludefile('jscript/common.js');
            addincludefile($incjsf);
            
            
            $tabcontentstyle.="
			.shiptype
	   		{
				position:absolute;
				left:10px;
				top:5px;   
		   		font-family : monospace; 
     			font-size : 12pt;								
	   		}
			.mybutt
			{
				position:relative;
				left:200px;
				top:5px;   
				
			}
			
			
			
			";
        }
        return $qrcnt>0;
    }

    function getlboxoption($val, $valname, $sel=false)
    {
        if ($sel) {
            $s=" selected";
        } else {
            $s="";
        }
        return '<option  value="'.$val.'"'.$s.'>'.$valname.'</option>';
    }

    
    function getpulldown($upgspec, $caption, $upgid, $qres, $qrcnt, $fldupgid, $fldupgname, $left, $top, &$selarr)
    {
        $label='lbl'.$upgspec;
        $upgidsel=$upgspec.'sel';
            
            
        $cont=" <div class='div$upgidsel'>
					<label class='lbl$upgidsel'>$caption</label>
					<select class='$upgidsel' name='$upgidsel' 	
					 	  onchange='".$upgidsel."changed($upgidsel,this.selectedIndex);'>							
			";
        $currow=null;
        $cont.=getlboxoption(0, 'Nothing', false);
        for ($i=1;$i<$qrcnt+1;$i++) {
            $xarr=query_fetch_array($qres);
            $vi=$xarr[$fldupgid];
            $v=$xarr[$fldupgname];
            if ($vi==$upgid) {
                $sel=true;
                $currow=$xarr;
            } else {
                $sel=false;
            }
            $cont.=getlboxoption($vi, $v, $sel);
        }
        $cont.="
				</select>
				
			";

        //return back

        $selarr=$currow;
        
        
        $jsfunc='
			
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
				    case "hull": armorval=obj.armor;
								 powerobj=getElementByName("propsellblpower");
								 powerval=powerobj.innerHTML;	
								
									varsend="speed=1&size="+sizeval+"&power="+powerval;
									remurl="Core/getupginfo.php";
				  					data=getAjaxData(remurl,varsend);
				  					var obj2 = jQuery.parseJSON(data);
								 	speedval=obj2.speed;								

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
        addjsfunction($upgidsel.'changed', $jsfunc, 'upgsel,selindex');


        $jsfunc='

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
        addjsfunction('recalcinfo', $jsfunc, '');
            
        $contstl="
				.div$upgidsel
				{
 				   position:absolute;
				   left:$left;
				   top:$top;
				   width:140px;
				   height:220px;
				   text-align:center;
				}
				.lbl$upgidsel
				{
				 position:absolute;	
				 left:0;
				 top:0;	
				 width:100%;
		   		 font-family : monospace; 
     			 font-size : 12pt;
				 text-align:center;
				}
				.$upgidsel
				{
				  position:absolute;
				  left:10px;	
				  top:30px;
				  width:130px;
				}
				.otherinfo
				{
				  position:absolute;
				  text-align:left;
				  top:60px;
				  left:10px;

				}
			";
            
        addstyle($contstl);
            
        
        return $cont;
    }
    
    function getupginfo($currow, $upg)
    {
        $size= getformatednumber($currow['size']);
        $gold= getformatednumber($currow['ngold']);
        $metalum= getformatednumber($currow['nmetalum']);
        $tritium= getformatednumber($currow['ntritium']);
            
        $cont="
				<div class='otherinfo'>
				<label>Size:</label>
				<label name='".$upg."sellblsize'>$size</label><br>
				<label>Gold:</label>
				<label name='".$upg."sellblgold'>$gold</label><br>
				<label>Metalum:</label>
				<label name='".$upg."sellblmetalum'>$metalum</label><br>
				<label>Tritium:</label>
				<label name='".$upg."sellbltritium'>$tritium</label><br>
				<br>
			";
            
        adddebugval('upg', $upg);
        switch ($upg) {
              case 'hull':
                $cont.="
  				   <label>Armor:</label>
				   <label name='".$upg."sellblarmor'>".$currow['armor']."</label><br>
			  			";
                      break;
              case 'prop':
              
                $speed=calculatespeed($currow['power'], $_SESSION['maxsize']);

                $cont.="
  				   <label>Power:</label>
				   <label name='".$upg."sellblpower'>".$currow['power']."</label><br>
  				   <label>Speed:</label>
				   <label name='".$upg."sellblspeed'>".$speed."</label><br>
				   
			  			";
                        
                      break;
              case 'comp':
                $cont.="
  				   <label>Accuracy:</label>
				   <label name='".$upg."sellblaccuracy'>".$currow['accuracy']."</label><br>
			  			";
                        
                      break;
              case 'sensor':
                $cont.="
  				   <label>Distance:</label>
				   <label name='".$upg."sellbldistance'>".$currow['distance']."</label><br>
			  			";
                        
                      break;
              case 'shield':
                $cont.="
  				   <label>Power:</label>
				   <label name='".$upg."sellblpower'>".$currow['power']."</label><br>
			  			";
                        
                      break;

              case 'weapon1':
              case 'weapon2':
              case 'weapon3':
                $cont.="
  				   <label>Distance:</label>
				   <label name='".$upg."sellblwdist'>".$currow['weapondist']."</label><br>
  				   <label>Damage:</label>
				   <label name='".$upg."sellblwdamg'>".$currow['weapondmg']."</label><br>				   
			  			";
                      break;


            }
        $cont.="	
				</div>
				";
                
                            
        return $cont;
    }
    
    function getshiptypeinfo2(&$tabcontent, &$tabcontentstyle, $shiptpid)
    {
        $uid=$_SESSION["id"];
        adddebugval('shiptype id', $shiptpid);
        $totsize=0;
        $totgold=0;
        $totmetalum=0;
        $tottritium=0;
        $left=00;
        $top=0;
           
        //hull
        $qres=getSelectedShipHull($shiptpid);
        if ($qres!=null) {
            $dbarr=query_fetch_array($qres);
            $qres=getAllShipHullsforUser($uid, $qrcnt);
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'hull',
                   'HULLS',
                   $dbarr['hullid'],
                   $qres,
                   $qrcnt,
                            'xhullid',
                   'hullname',
                   $left,
                   $top,
                   $sel
               );
                $tabcontent.=getupginfo($sel, 'hull');
                $tabcontent.='</div>';
                $_SESSION['shipimg']=$sel['image'];
                $_SESSION['maxsize']=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }

            //computer
            $qres=getAllShipComputersforUser($uid, $qrcnt);
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'comp',
                   'COMPUTERS',
                   $dbarr['computerid'],
                   $qres,
                   $qrcnt,
                            'xcompid',
                   'compname',
                   $left+150*1,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'comp');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }

            //propulsion
            
            
            $qres=getAllShipPropulsionsforUser($uid, $qrcnt);
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'prop',
                   'PROPULSION',
                   $dbarr['propulsionid'],
                   $qres,
                   $qrcnt,
                            'xpropid',
                   'propname',
                   $left+150*2,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'prop');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            //sensors
            $qres=getAllShipSensorsforUser($uid, $qrcnt);
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'sensor',
                   'SENSORS',
                   $dbarr['sensorid'],
                   $qres,
                   $qrcnt,
                            'xsensid',
                   'sensname',
                   $left+150*3,
                   $top,
                   $sel
               );
                $tabcontent.=getupginfo($sel, 'sensor');
                $tabcontent.='</div>';

                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            //shields


            
            
            $qres=getAllShipShieldsforUser($uid, $qrcnt);
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'shield',
                   'SHIELDS',
                   $dbarr['shieldid'],
                   $qres,
                   $qrcnt,
                            'xshieldid',
                   'shieldname',
                   $left+150*4,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'shield');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            
            //Weapons

            //$quer="SELECT * FROM `x_weapons` order by `size`";
            
            //$quer="SELECT * from x_weapons where concat('XW',xweaponid) in
            //         (select xtraid as xid from `techuser`,`technology` where techuser.techid=technology.techid  and userid=$uid) order by `size`";
            $qres=getAllShipWeaponsforUser($uid, $qrcnt);
            
            if ($qres!=null) {
                $tabcontent.=getpulldown(
                   'weapon1',
                   'WEAPON 1',
                   $dbarr['weapon1id'],
                   $qres,
                   $qrcnt,
                            'xweaponid',
                   'weaponname',
                   $left+150*5,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'weapon1');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            //	$quer="SELECT * FROM `x_weapons` order by `size`";


            if ($qres!=null) {
                $mysqli->data_seek($qres, 0);
                $tabcontent.=getpulldown(
                   'weapon2',
                   'WEAPON 2',
                   $dbarr['weapon2id'],
                   $qres,
                   $qrcnt,
                            'xweaponid',
                   'weaponname',
                   $left+150*6,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'weapon2');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            //	$quer="SELECT * FROM `x_weapons` order by `size`";
            if ($qres!=null) {
                $mysqli->data_seek($qres, 0);
                $tabcontent.=getpulldown(
                   'weapon3',
                   'WEAPON 3',
                   $dbarr['weapon3id'],
                   $qres,
                   $qrcnt,
                            'xweaponid',
                   'weaponname',
                   $left+150*7,
                   $top,
                   $sel
               );

                $tabcontent.=getupginfo($sel, 'weapon3');
                $tabcontent.='</div>';
                $totsize+=$sel['size'];
                $totgold+=$sel['ngold'];
                $totmetalum+=$sel['nmetalum'];
                $tottritium+=$sel['ntritium'];
            }
            
            
            $tabcontent.="<div >";
            
            $tabcontent.="</div>";
            
            $_SESSION['totsize']=$totsize;
            $_SESSION['totgold']=$totgold;
            $_SESSION['totmetalum']=$totmetalum;
            $_SESSION['tottritium']=$tottritium;
        }
    }
    

    function getshipbuild2(&$tabcontent, &$tabcontentstyle)
    {
        global $userid,$selindex;
        
        $quer="SELECT * FROM `shiptypes`,`x_hulls` where (ownerid=$userid) and (hullid=xhullid) order by `size`";
        
        adddebugval('selidx', $selindex);
        
        if (executequery($quer, $qres, $qrcnt)) {
            $tabcontent.="	
			  <div class='shipsel'> 	
			   <form method='post' name='delship' id='delship' value='submit'>		 
				<select name='shiptype' class='shiptype' SIZE=11 style='' 
				     onchange='newimage(this.selectedIndex);' >					 			  		 
			";
            if (!myisset($shipsel)) {
                if (myisset($_SESSION['shipsel'])) {
                    $shipsel=$_SESSION['shipsel'];
                } else {
                    $shipsel=null;
                }
            }
            if ($shipsel==null and $selindex==null) {
                $selindex=1;
            }
            $selid=-1;
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                $v=$dbarr['stypename'];
                $vi=$dbarr['stid'];
                if (($selindex!=null) and ($selindex==$i)) {
                    $shipsel=$vi;
                }
                                        
                if ($vi==$shipsel) {
                    //				  $_SESSION['shipimg']=$dbarr['image'];
                    $selid=$vi;
                }
                $tabcontent.=getlboxoption($vi, $v, $vi==$shipsel);
            }
            $tabcontent.="</select>
			<input type='submit' class='mybutt2' value='Delete ship'  />
		    <input type='hidden' name='submit' value='submit2' />
			</form>
			 </div>";
            
            $tabcontent.="
				<form method='post' name='newship' id='newship' value='submit'>
				<div class='shipupgs'>
				";
            
            getshiptypeinfo2($tabcontent, $tabcontentstyle, $selid);
            
            $tabcontent.="
				</div>
			";


            $totsize=getformatednumber($_SESSION['totsize']);
            $maxsize=getformatednumber($_SESSION['maxsize']);
            $totgold=getformatednumber($_SESSION['totgold']);
            $totmetalum=getformatednumber($_SESSION['totmetalum']);
            $tottritium=getformatednumber($_SESSION['tottritium']);

            $tabcontent.="
				<div class='newship'>
				<table width='600' border='0' cellspacing='1' cellpadding='0'>
  				 <tr>
    				<th scope='col'>Name</th>
					<th scope='col'>Max Size</th>
    				<th scope='col'>Size</th>
    				<th scope='col'>Gold</th>
    				<th scope='col'>Metalum</th>
    				<th scope='col'>Tritium</th>
  				 </tr>
  				 <tr>
    				<td align='center'><input type='text' name='shipname' id='shipname' value='New Ship' style='width:140; align:center'/></td>
					<td align='center'><label name='maxsize' class='labelno' />$maxsize</td>
    				<td align='center'><label name='size' class='labelno'/>$totsize</td>
    				<td align='center'><label name='gold' class='labelno'/>$totgold</td>
    				<td align='center'><label name='metalum' class='labelno'/> $totmetalum</td>
				    <td align='center'><label name='tritium' class='labelno'/>$tottritium</td>
				 </tr>
				</table>
				</div>
				<div style='float:left'>		
  			  <input type='submit' class='mybutt' value='Create new ship'  />
			  <input type='hidden' name='submit' value='submit' />
			  </div>
			  </form>
			  
			";
            
            $jscr="		
							
				$(function() {
				    $( 'input[type=submit]' )
      				.button()
      				.click(function( event ) {
        				
      					});
  				});
			";
            
            addjscript($jscr);
            
            
            $_SESSION['shipsel']=$shipsel;
            
            $jsfunc='
			
				si=selindex+1;
				
				if (window.location.href.indexOf("selindex")>0) {
  				   s=getMainURL()+"?"+changeQueryVariable("selindex",si);
				   window.location.href=s;
				}
				else
  				  window.location.href=window.location.href+"&selindex="+si;
			';
            addjsfunction('newimage', $jsfunc, 'selindex');
            
        
            $incjsf=getjsincludefile('jscript/common.js');
            addincludefile($incjsf);
            
            
            $tabcontentstyle.="
			.labelno
			{
				width:80;
				background-color:#ffff00;
				display:block; 
				clear:left;		
				text-align:right;	
				color:black;	
			}
			.shipsel
	   		{
				position:absolute;
				left:10px;
				top:5px;   
				width:180px;
				height:240px;
	   		}
			.shiptype
	   		{
				position:relative;
				left:00px;
				top:0px;   
				width:170px;
				height:240px;
		   		font-family : monospace; 
     			font-size : 12pt;

	   		}
			.shipupgs{
				position:absolute;
				overflow: auto;
				left:185px;
				top:5px;   
				width:800px;
				height:245px;								
			}
			.newship
			{
				position:absolute;
				left:10px;
				top:330px;   
				width:650px;
				height:50px;

			}
			.mybutt
			{
				position:absolute;
				left:800px;
				top:330px;   
				
			}
			.mybutt2
			{
				position:absolute;
				left:10px;
				top:240px;   
				
			}
			
			
			
			";
        }
        return $qrcnt>0;
    }



    function showshipdesign(&$pgcontent, &$pgcontentstyle)
    {
        global $selplanet;
        
        
        //test tabs
        $tabarr[0]='Ships';
        
        if (myisset(filter_input(INPUT_GET, 'tab'))) {
            $tbsel=filter_input(INPUT_GET, 'tab');
        } else {
            if (myisset($_SESSION['shiptab'])) {
                $tbsel=$_SESSION['shiptab'];
            } else {
                $tbsel=0;
            }
        }
        $tabcnt=count($tabarr);
        if ($tbsel>=$tabcnt) {
            $tbsel=$tabcnt-1;
        }
        
        $tabcontent="";
        $tabcontentstyle="";
        switch ($tbsel) {
         case 0:
            if (getshipbuild2($tabcontent, $tabcontentstyle)) {
                showshipimage($pgcontent, $pgcontentstyle);
            }

         
         break;
         
         default:
          $tabcontent="";$tabcontentstyle="";
        }
        $_SESSION['shiptab']=$tbsel;
        createtab($tabarr, 05, 180, 1000, 440, $tbsel, $tabcontent, $tabcontentstyle);
        
        $pgcontent.=$tabcontent;
        $pgcontentstyle.=$tabcontentstyle;
    }




//-----------------------
function init_page()
{
    global $selindex;

    getsessionvars();
    if (myisset(filter_input(INPUT_GET, 'selindex'))) {
        $selindex=filter_input(INPUT_GET, 'selindex');
        adddebugval('sidx', $selindex);
    } else {
        $selindex=null;
    }
         
    if (myisset(filter_input(INPUT_POST, 'submit'))) {
        if (filter_input(INPUT_POST, 'submit')=='submit') {
            $selindex=null;
            //  adddebugval('building new ship');
            buildnewship();
        }

        if (filter_input(INPUT_POST, 'submit')=='submit2') {
            $shipid=filter_input(INPUT_POST, 'shiptype');
            adddebug("Delete $shipid shiptype<br>");
            deleteship($shipid);
        }
    }
    $pgcontent='<div name="shipdesign" class="shipdesign">';
    $pgcontentstyle='
	  .shipdesign
	  {
		position:absolute;  
	    left:12px;
		top:129px;
		width:1000px;
		height:640px;					
	  }
	  
	  ';
    adddebugval('si', $selindex);
    showshipdesign($pgcontent, $pgcontentstyle);
    
    $pgcontent.='</div>';
    addoutput($pgcontent, $pgcontentstyle);
}
?>