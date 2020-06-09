<?php

include_once "planetutils.php";
include_once "galaxyutils.php";

function showplanetinfo(&$ajaxcode, $isajax = null)
{
    if ($isajax == null) {
        if (myisset(getsessionvar('isajax'))) {
            $isajax = $_SESSION['isajax'];
        } else {
            $isajax = false;
        }
    }


    $selplanet = $_SESSION['selplanet'];


    $dbarr = getplanetinfo($selplanet);
    $usrarr = getuserdatabyid();

    getplanetowner($selplanet, $uid, $uname);
    if ($uid == 0) {
        $uname = "NoOne";
    }
    //		  <img class='imgplaninfo' src='Images/infobg.png'>

    $gp = $dbarr['goldprod'];
    $mp = $dbarr['metalumprod'];
    $tp = $dbarr['tritiumprod'];
    getplanethourprod($selplanet, $gp, $mp, $tp);
    $r1 = $gp - $dbarr['goldprod'];
    $r2 = $mp - $dbarr['metalumprod'];
    $r3 = $tp - $dbarr['tritiumprod'];
    $plname = $dbarr['name'];
    $coords = $dbarr['coordx'] . ':' . $dbarr['coordy'];
    $pltype = $dbarr['typename'];
    $plsize = $dbarr['size'];
    $planetmaxpop = $dbarr['maxpopulation'];
    $planetmaxpopstr = bd_nice_number($planetmaxpop, false);
    $maxpop = calcpeopleaccom($selplanet);
   // $maxpophint = getformatednumber($maxpop);//detailed population
    $maxpopstr = bd_nice_number($maxpop);
    $planimg = $dbarr['imagename'] . "64";

    $pop = $dbarr['population'];
    $pophint = getformatednumber($pop);
    $popstr = bd_nice_number($pop, false);

    $gold = $dbarr['gold'];
    $metalum = $dbarr['metalum'];
    $tritium = $dbarr['tritium'];
    $golds = getformatednumber($gold);
    $metalums = getformatednumber($metalum);
    $tritiums = getformatednumber($tritium);
    $gtobuild = $dbarr['goldtobuild'];
    $mtobuild = $dbarr['metalumtobuild'];
    $ttobuild = $dbarr['tritiumtobuild'];

    $tpoints = floor($usrarr['techpoints']);
    $tpointstr = getformatednumber($tpoints);

    $plninfo = "
		   <div class='plname tooltip'>
                     <span class='tooltiptext'>planet name</span>
		     <b>$plname</b> [ $coords ]		   
		   </div>
   		   <div class='plsize'>
		     <b>$pltype($plsize)</b> 		   
		   </div>
		   <div class='username tooltip'>
                     <span class='tooltiptext'>user name</span>
		     <b>$uname($uid)</b> 		   
		   </div>
                   <div class='planetimage'>
                     <img class='upplanimg' src='Images/$planimg.png'>
                   </div>
                   <div class='techpoints tooltip'>
                     <span class='tooltiptext'>total tech points</span>
		     <b>$tpointstr</b> 		   
		   </div>
                   <div class='popno tooltip'>
                     <span class='tooltiptext'>population <br> $pophint of $maxpopstr <br> (max is $planetmaxpopstr)</span>
		     <b>$popstr</b> 		   
		   </div>
                   <div class='goldno tooltip' id='goldno'>
                     <span class='tooltiptext'>gold on planet</span>
		     <b>$golds</b> 		   
		   </div>
                   <div class='metalno tooltip' id='metalumno'>
                     <span class='tooltiptext'>metalum on planet</span>
		     <b>$metalums</b> 		   
		   </div>
                   <div class='tritno tooltip' id='tritiumno'>
                     <span class='tooltiptext'>tritium on planet</span>
		     <b>$tritiums</b> 		   
		   </div>
                   <div class='goldph tooltip'>
                     <span class='tooltiptext'>gold production <br> per hour</span>
		     <b>$gp</b> 		   
		   </div>
                   <div class='metalph tooltip'>
                     <span class='tooltiptext'>metalum production <br> per hour</span>
		     <b>$mp</b> 		   
		   </div>
                   <div class='tritph tooltip'>
                     <span class='tooltiptext'>tritium production <br> per hour</span>
		     <b>$tp</b> 		   
		   </div>
                   <div class='goldbnsph tooltip'>
                     <span class='tooltiptext'>gold bonus <br> per hour</span>
		     <b>+$r1</b> 		   
		   </div>
                   <div class='metalbnsph tooltip'>
                     <span class='tooltiptext'>metalum bonus <br> per hour</span>
		     <b>+$r2</b> 		   
		   </div>
                   <div class='tritbnsph tooltip'>
                     <span class='tooltiptext'>tritium bonus <br> per hour</span>
		     <b>+$r3</b> 		   
		   </div>		
		";

    $plnstyle = "
		  
		";

    $jscript = "
					var gp=$gp;var mp=$mp;var tp=$tp;					
					var gold=$gold;
					var metalum=$metalum;
					var tritium=$tritium;
					var gtobuild=$gtobuild;
					var mtobuild=$mtobuild;
					var ttobuild=$ttobuild;															
					var goldadd=gp-gtobuild;
					var metalumadd=mp-mtobuild;
					var tritiumadd=tp-ttobuild;										
					var newGold=-1;
					var newMetalum=-1;
					var newTritium=-1;
					
					
					function getProduction(prod){
					  return (intrval/1000)*prod/(60*60);	
					}
					
					function getCurrentProd(prodname){
					   prodinfo=document.getElementById(prodname);
  				           return toNumber(prodinfo.innerText);
					}
					
					function setCurrentProd(prodname,v){
						prodinfo=document.getElementById(prodname);
						prodinfo.innerText=formatNumber(v);
					}
					
		 			function updateplanetinfo(){
 					  goldlbl=getCurrentProd('goldno');
					  metalumlbl=getCurrentProd('metalumno');
					  tritiumlbl=getCurrentProd('tritiumno');
					  if (newGold<0) newGold=gold;
					  if (newMetalum<0) newMetalum=metalum;
					  if (newTritium<0) newTritium=tritium;
					     //hour in secs ngp=prod per sec	
					  newGold+=getProduction(goldadd);
					  newMetalum+=getProduction(metalumadd);
					  newTritium+=getProduction(tritiumadd);
						//TODO:subtract the automatic resource allocated for buildings
						
					  if (Math.floor(newGold)!=goldlbl)
					     setCurrentProd('goldno',Math.floor(newGold));
					  if (Math.floor(newMetalum)!=metalumlbl)
					     setCurrentProd('metalumno',Math.floor(newMetalum));
					  if (Math.floor(newTritium)!=tritiumlbl)
					     setCurrentProd('tritiumno',Math.floor(newTritium));
						 
					}
					
				";

    $jscript2 = "
					var intrval=5000;
					var myVar=null

					function refreshPlanetInfo(){
					   obj=getAjaxInfo(999,'planetinfo','p=0','plninfoscript');	
			   		   divobj=document.getElementById('planetinfo'); 
			   		   divobj.innerHTML=obj.content;	
						
					}
		
					function startupdate(){
					  refreshPlanetInfo();
					  if (myVar!=null) window.clearInterval(myVar);
					  myVar=setInterval(function(){updateplanetinfo()},intrval);
					}
		
		";


    if (myisset(getsessionvar('isajax'))) {
        adddebugval('ajax', $_SESSION['isajax']);
    }
    if ($isajax == false) {
        addoutput($plninfo, $plnstyle);
        addjscript($jscript2);
        addonloadfunction('startupdate();');
        adddebugval('PLANET', $selplanet);
    } else {
        $ajaxcode .= $jscript;
        return "<style type='text/css'>" . $plnstyle . "</style>" . $plninfo;
    }
}

if (myisset(filter_input(INPUT_GET, 'info'))) {
    db_connect();
    activityoccur();

    $ajaxcode = '';
    $retarr['content'] = showplanetinfo($ajaxcode, true);
    $retarr['scriptcode'] = $ajaxcode;
    getdebugdata($retarr);
    echo json_encode($retarr);
}
