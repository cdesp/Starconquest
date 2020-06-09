<?php

function getroutelist(&$tabcontent, &$tabcontentstyle, &$jscript, $usrid = null)
{
    $dbres = getalluserroutes($usrid);
    $reccnt = query_num_rows($dbres);
    adddebugval('routecnt', $reccnt);

    if (myisset(getsessionvar('selfleet'))) {
        $fltsel = $_SESSION['selfleet'];
    }

    $divheight = 60;

    $tabcontent = "
                        <div class='rtmaintit'><div class='noofroutes'>[$reccnt]</div></div>			
                        <div class='divroutelist'>
		";
    $jscript .= "var rt = new Array();var rtcnt=$reccnt;";
    for ($i = 0; $i < $reccnt; $i++) {
        $dbarr = query_fetch_array($dbres);


        $tp = 0 + $i * ($divheight + 0);

        $rtid = $dbarr['rtid'];
        $fltid = $dbarr['fltid'];
        $divname = "divroute_$fltid";
        $coordx = $dbarr['curcoordx'];
        $coordy = $dbarr['curcoordy'];
        $tocoordx = $dbarr['tocoordx'];
        $tocoordy = $dbarr['tocoordy'];
        $fspeed = $dbarr['fspeed'];
        $feta = $dbarr['eta'];
        $fetas = date("d-m-Y H:i:s", $feta);
        $f2 = ($feta);
        $jscript .= "rt[$i]=$f2;";
        $dur = $feta - mtimetn();

        $durs = getshiptime($dur);

        $fltres = getfleetbyid($fltid);
        $fltarr = query_fetch_array($fltres);
        $fltname = $fltarr['fltname'];

        if (!myisset($fltsel)) {
            $fltsel = $fltid;
        }
        $col = '#43C3C3';
        $bimgname = 'allroutelist';
        if ((myisset($fltsel)) and($fltsel == $fltid)) {
            $issel = true;
            $_SESSION['routesel'] = $rtid;
            $col = '#4363C3';
            $bimgname = 'allroutelist_sel';
            // $divname.=' divroute_sel';
        } else {
            $issel = false;
        }
        adddebugval('routesel', $_SESSION['routesel']);

        $tabcontent .= "
			   <div class='$divname divroute'>
                                 <div class='rtfname'>
				   <a href='javascript:selectFleet($fltid);'>
				    $fltname</a> 				   
				 </div>
                                 <div class='rtcoords'>
                                   [$coordx:$coordy]->[$tocoordx:$tocoordy]                                     
                                 </div>				
				 <div class ='rtinfo'>
                                   <div class ='rtinfo_speed'>
				    Fleet speed: $fspeed
                                   </div>
                                   <div class ='rtinfo_eta'>
				     E.T.A. : $fetas 
                                   </div>
                                   <div class ='rtinfo_duration'>
				 	Duration :  <label id='rt_$i'> $durs  </label>
                                   </div>         
				 </div>
				</div>				 
			  ";


        $tabcontentstyle .= "
			  .$divname
			  {
				top:$tp;
				height:$divheight;
                                background-image:url('/Images/$bimgname.png');
			  }
			 ";
    }


    $tabcontent .= "
			</div'>			
		";



    $tabcontentstyle .= "

		 ";
    $stime = mtimetn();
    $jscript .= "	
					var servertime=$stime;var srvdiff=$.now()/1000 - servertime;
				
					function doupdate(){
						curtime= $.now()/1000 ;
						for (i=0;i<rtcnt;i++){
							nm='rt_'+i;
						  eta=rt[i];
						  dif=eta-curtime+srvdiff;
                                                  if (dif<=0) tabpressed(null,2);
						  //console.log(dif);
						  //console.log(formatTime(dif));
						  elm=document.getElementById(nm);
						  if (typeof elm !='undefined' && elm!=null)					  
   					        elm.innerText=''+formatTime(dif)+'';
						  else clearInterval(myint); 	
						}
						
					}
				
					function initform(){	
						
						//console.log('if '+selfleet);
						if (typeof myint!='undefined') clearInterval(myint); 
						myint=setInterval(doupdate,1000);
						if (selfleet>0)
					     scrollToView('divfleet_'+selfleet);						
					}
					
					function selectFleet(fltid){
						//console.log('NEW FLEET SELECT');
						setAjaxSessionParam('selfleet',fltid);
						setAjaxSessionParam('action','fleetcenter');
						if (selfleet>0)
						    $('.divroute_'+selfleet).css('background-image','url(" . '"' . "/Images/allroutelist.png" . '"' . ")');
                                                $('.divroute_'+fltid).css('background-image','url(" . '"' . "/Images/allroutelist_sel.png" . '"' . ")');						
						selfleet=fltid;
					//	console.log('sf='+selfleet);
						refreshMap();
						
						//tabpressed(null,selectedTab);//reload tab
					}
					
				";
}

$_SESSION['showroutes'] = 1;
