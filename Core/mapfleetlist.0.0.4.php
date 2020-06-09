<?php

include_once("shiputils.php");
include_once("myutils.php");

function getfleetlist(&$tabcontent, &$tabcontentstyle, &$jscript, $usrid = null)
{
    $dbres = getalluserfleets($usrid);
    $reccnt = query_num_rows($dbres);
    if (myisset(getsessionvar('selfleet'))) {
        $fltsel = $_SESSION['selfleet'];
    } else {
        $fltsel=null;
    }

    $divheight = 90;

    $tabcontent = "
                    <div class='maintit'><div class='nooffleets'>[$reccnt]</div></div>			
                    <div class='divfleetlist'>			
		";

    for ($i = 0; $i < $reccnt; $i++) {
        $dbarr = query_fetch_array($dbres);


        $tp = 0 + $i * ($divheight);

        $fltid = $dbarr['fltid'];
        $divname = "divfleet_$fltid";
        $fltname = $dbarr['fltname'];
        $coordx = $dbarr['coordx'];
        $coordy = $dbarr['coordy'];
        $sclass = '';

        if ((myisset($fltsel)) and($fltsel == $fltid)) {
            $issel = true;
            $sclass = ' divfleet_sel';
            $rtid = getfleetonroute($fltid);

            $_SESSION['routesel'] = $rtid;
        } else {
            $issel = false;
        }

        //			adddebugval('divname',$divname);
        $tabcontent .= "
			   <div class='$divname $sclass divfleet allfleetlist' id='$divname'>
			     <div class='fname'>
				   <a href='javascript:selectFleet($fltid);'>
				    $fltname</a><br> 
				   [$coordx:$coordy]					
				 </div>
				 <div class='ships'>
				 
			<table class='shipinfo'  border='0' cellspacing='1' cellpadding='0'>
			  <tbody style='height: 70px; overflow: auto'>
			  ";

        $shpres = getshipsoffleet($fltid);
        $shpcnt = query_num_rows($shpres);



        for ($si = 0; $si < $shpcnt; $si++) {
            $shparr = query_fetch_array($shpres);
            $shparr = getshipinfo($shparr);
            $shpname = $shparr['stypename'];
            $maxsize = getformatednumber($shparr['maxsize']);
            $speed = $shparr['speed'];
            $quant = $shparr['quantity'];
            $armor = $shparr['armor'];

            $tabcontent .= " 
         			  <tr>
			            <td><b>$shpname</b></td>
			    		<td>$quant</td>
			    		<td>$maxsize</td>
			    		<td>$speed</td>
			    		<td>$armor</td>
			    		
			  		</tr>
			 	 ";
        }
        $tabcontent .= "  
			   </tbody>
			  </table>		
				 
				 
				 </div>

			   
			   
			   
			   </div>
			";

        $tabcontentstyle .= "
			  .$divname
			  {
				top:$tp;				
			  }
			 ";
        if ($issel) {
            $tabcontentstyle .= "
		     ";
        }
    }

    $tabcontent .= "
			</div>
			
		";



    $tabcontentstyle .= "
         
		 ";


    $jscript = "	
					function initform(){	
						
						console.log('if '+selfleet);
						if (selfleet>0){
						 console.log('scrolltoview '+selfleet);
					         scrollToView('divfleet_'+selfleet);						
						}
					}
					
					function selectFleet(fltid){
						console.log('NEW FLEET SELECT('+fltid+'<--'+selfleet+')');
						setAjaxSessionParam('selfleet',fltid);
						setAjaxSessionParam('action','fleetcenter');
                                                //console.log('prev sel divfleet_'+selfleet);
						if (selfleet>0)
						    $('#divfleet_'+selfleet).css('background-image','url(" . '"' . "/Images/allfleetlist.png" . '"' . ")');
                                                //console.log('new sel divfleet_'+fltid);
						$('#divfleet_'+fltid).css('background-image','url(" . '"' . "/Images/allfleetlist_sel.png" . '"' . ")');							
						selfleet=fltid;
						//console.log('sf='+selfleet);
						refreshMap();
						
						//tabpressed(null,selectedTab);//reload tab
					}
					
				";
}
