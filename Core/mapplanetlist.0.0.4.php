<?php
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once("gamesession.php");


    function getplanetlist(&$plnout, &$plnstl, &$jscript, $usrid=null)
    {
        $userid=$_SESSION['id'];
        $selplanet=$_SESSION['selplanet'];
        
        $dbres=getalluserplanets($usrid);
        $reccnt=query_num_rows($dbres);
        if (myisset(filter_input(INPUT_GET, 'planinfo'))) {
            $_SESSION['planinfo']=filter_input(INPUT_GET, 'planinfo');
        }
          
        if (!myisset(getsessionvar('planinfo'))) {
            $_SESSION['planinfo']=1;
        }
        $infotp=$_SESSION['planinfo'];

        $divheight=95;

        $tmppl="
		  <div class='infoimages'>
			<a href='javascript:setplanetinfo(1);'><img class='infoimg1' src='Images/info1.png' title='Production'> 
			</a>				
			<a href='javascript:setplanetinfo(2);'><img class='infoimg2' src='Images/info2.png' title='Ships'> 
			</a>				
		  </div>	
			
		";

        //if ($infotp==1)
          
        $tmppl= "
				 <div class='listheader'>
                                    $tmppl
				 </div>		  					
		   ";
        /*else
          $tmppl="
                 <div class='listheader'>
                                       $tmppl
                 </div>
          ";*/
             
             
        $tmppl.="<div class='planlist myscroller '>";
        
        $tmpst=" 
				.infoimages
				{
				  position:absolute;
				  left:5;
				  top:2;	
				  width:52;
				  height:24;	
				  #background-color:red;                                  
				}
				.infoimg1
				{
				  position:relative;
				  left:0;
				  top:0;
				  width:24;
				  height:24;
				}
				.infoimg2
				{
				  position:relative;
				  left:3;
  				  top:0;
				  width:24;
				  height:24;
				}		
				.planlist
				{
   				   position: absolute;
  				    display: block;
 				    z-index: 1;
                                    top: 30px;
  				    left: 0px;	
                                    width:500;
                                    height:530px;
				 // background-color:#6495ED;
                                    overflow:auto;
				}
				.listheader
				{
   				   position: absolute;
                                   top:0px;	
                                   left:8px;
                                   width:480px;
                                   height:30px;
                                   background-image: url('Images/planetlistheader.png');
				}
				.lhd1
				{
					position: absolute;
 		  		   font-family:arial;color:black;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:85;
				   top:2px;
				}
				.lhd2
				{
					position: absolute;
 		  		   font-family:arial;color:white;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:190;					
				   top:2px;
				}
				.lhd3
				{
					position: absolute;
 		  		   font-family:arial;color:#666633;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:320;					
				   top:2px;
				}
				
		";
        
        
        
        $bgcol1='#9bbbd0';//Selected
        $bgcol2='#9bbbd0';//not selected
        
        for ($i=0;$i<$reccnt;$i++) {
            $dbarray=query_fetch_array($dbres);
            if ($dbarray==false) {
                break;
            }
            $pid=$dbarray['pid'];
            $sclass="";
            $sid="planet_$pid";
            if ($selplanet==$pid) {
                $bgcol=$bgcol1;
                $sclass="selplanet";
            } else {
                $bgcol=$bgcol2;
            }
            if ($selplanet==$pid) {
                $opac=1.0;
            } else {
                $opac=0.6;
            }
          
          
            $gp=$dbarray['goldprod'];
            $mp=$dbarray['metalumprod'];
            $tp=$dbarray['tritiumprod'];
            getplanethourprod($pid, $gp, $mp, $tp);
            $g=getformatednumber($dbarray['gold']);
            $m=getformatednumber($dbarray['metalum']);
            $t=getformatednumber($dbarray['tritium']);

            $tmppl.="
		 <div class='planetdiv planetdiv_$pid $sclass myemboss' id='$sid'>
                  <div class='plnimgname myinset'>
	     	    <a href='javascript:selectPlanet($pid);'><div class='plnname' >".$dbarray['name']."</div><img class='planimg' src='Images/".$dbarray['imagename']."48.png'> 
                            <a href='?pg=planet'> <div class='plnenter' ></div></a>
			<br></a>
                  </div>
		  
		  ";
          
            if ($infotp==1) {
                $tmppl=$tmppl. "
							<div class='gold' >	
							   <div class='hd4 myinset'>".$g." </div> 
                                                           <div class='hd41 tooltip myinset'>
                                                             <span class='tooltiptext'> 
                                                               per hour
                                                             </span>
                                                             $gp
                                                            </div>     
							</div>
							<div class='metalum' >	
							   <div class='hd4 myinset'>".$m." </div> 
                                                           <div class='hd41 tooltip myinset'>
                                                             <span class='tooltiptext'> 
                                                               per hour
                                                             </span>
                                                             $mp
                                                            </div>     
							</div>
							<div class='tritium' >	
							   <div class='hd4 myinset'>".$t." </div> 
                                                           <div class='hd41 tooltip myinset'>
                                                             <span class='tooltiptext'> 
                                                               per hour
                                                             </span>
                                                             $tp
                                                            </div>     

							</div>
		
					 ";
            } else {
                if ($usrid!=null) {
                    $qres=getshipsonplanet($pid, $usrid, $qrcnt);
                } else {
                    $qres=getshipsonplanet($pid, $userid, $qrcnt);
                }
                $tmppl.="<div class='ships' >
			";
            
                if ($qres!=false and $qrcnt>0) {
                    $tmppl.="
			<table class='shipinfo'  border='0' cellspacing='1' cellpadding='0'>
			 <thead>
			  <tr>
 			    <th scope='col'>Name</th>
			    <th scope='col'>Quant.</th>
				<th scope='col'>Size</th>
			    <th scope='col'>Speed</th>			    
			    <th scope='col'>Armor</th>
				<th scope='col'></th>
			  </tr>
			  </thead>
			  <tbody style='height: 70px; overflow: auto'>
			";
                
                    for ($j=0;$j<$qrcnt;$j++) {
                        $qrows= query_fetch_array($qres);
                        $qrows=getshipinfo($qrows);
                        $stid=$qrows['stid'];
                        $shpname=$qrows['stypename'];
                        $maxsize=getformatednumber($qrows['maxsize']);
                        $speed=$qrows['speed'];
                        $quant=$qrows['quantity'];
                        $armor=$qrows['armor'];
                
                        if ($quant>0) {
                            $tmppl.=" 
         			  <tr>
			            <td><b>$shpname</b></td>
			    		<td>$quant</td>
			    		<td>$maxsize</td>
			    		<td>$speed</td>
			    		<td>$armor</td>
			    		<td></td>						
			  		</tr>
			 	 ";
                        }
                    }
              
                    $tmppl.= "
			  </tbody>
			  </table>
			 ";
                } else {
                    $tmppl.= "No ships on planet";
                }

                $tmppl.="</div>";
            }
          
            $tmppl.="
					<br>
					</div>			  
		  		  ";
                  
            $divtp=02+$i*$divheight;
            $tmpst=$tmpst."
			.planetdiv_$pid
			{
   			  position: absolute;
   			  display: block;
                          z-index: 0;
                          top: ".$divtp."px;
                          left: 2px;	
	  		  width: 480px;
	  		  height: ".$divheight."px;
	 		  #background-color:$bgcol;
	 		  #background-image: url('Images/planetlist.png');
	 		  opacity:$opac;
			}
			";
        } //end for

      
        $tmpst=$tmpst."
	";

        $jscript.="	
					
		
					function checkbackground(){
						var plinf=getAjaxSessionParam('planinfo');
						console.log('PLANINFO='+plinf);
						if (plinf==1){
						 // $('.planetdiv').css('background-image', 'url(Images/planetlist.png)');
                                                  $('.listheader').css('background-image', 'url(Images/planetlistheader.png)');
                                                  
                                                }
						else {
  						 // $('.planetdiv').css('background-image', 'url(Images/planetfleetlist.png)');						
                                                  $('.listheader').css('background-image', 'url(Images/fleetlistheader.png)');                                                  
                                                }
						
					}


					function initform(){
						if (selplanet>0)
					     scrollToView('planet_'+selplanet);
						checkbackground();
					}

					
					function setplanetinfo(planinfo){
						setAjaxSessionParam('planinfo',planinfo);												  
						tabpressed(null,selectedTab);//reload tab						
						checkbackground();
					}
					
					function selectPlanet(pid){
						//$('#planet_'+selplanet).css('background-color','".$bgcol1."');
						$('#planet_'+selplanet).css('opacity','0.6');
						setAjaxSessionParam('selplanet',pid);
						selplanet=pid;
						setAjaxSessionParam('action','planetcenter');
						refreshMap();
						//$('#planet_'+pid).css('background-color','".$bgcol2."');
						$('#planet_'+pid).css('opacity','1.0');
						//tabpressed(null,selectedTab);//reload tab

					}
				";


        $tmppl.="</div>";

        $plnout=$tmppl;
        $plnstl=$tmpst;
    }
