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

        if ($infotp==1) {
            $tmppl.= "
				 <div class='listheader'>
				 	<label class='lhd1'>GOLD</label>
					<label class='lhd2'>METALUM</label>
					<label class='lhd3'>TRITIUM</label>
				 </div>		  					
		   ";
        } else {
            $tmppl.="
				 <div class='listheader'>
					<label class='lhd2' style='left:120;width:200'>SHIPS ON PLANETS</label>
				 </div>		  							  
		  ";
        }
             
             
        $tmppl.="<div class='planlist'>";
        
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
				  left:0;
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
  				    left: 5px;	
					  width:495;
				  height:515;
				 // background-color:#6495ED;
				  overflow:auto;
				}
				.listheader
				{
   				   position: absolute;
					top:0px;					
				}
				.lhd1
				{
					position: absolute;
 		  		   font-family:arial;color:black;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:85;
				   top:5px;
				}
				.lhd2
				{
					position: absolute;
 		  		   font-family:arial;color:white;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:190;					
				   top:5px;
				}
				.lhd3
				{
					position: absolute;
 		  		   font-family:arial;color:#666633;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:320;					
				   top:5px;
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
		 <div class='planetdiv planetdiv_$pid $sclass' id='$sid'>	
	     	<a href='javascript:selectPlanet($pid);'><img class='planimg' src='Images/".$dbarray['imagename']."48.png'> 
			<div class='plnname' >".$dbarray['name']."</div><br></a>
		  
		  ";
          
            if ($infotp==1) {
                $tmppl=$tmppl. "
							<div class='gold' >	
							   <text class=hd4>".$g." <br> ".$gp." / hour</text> 
							</div>
							<div class='metalum' >	
							   <text class=hd4>".$m." <br> ".$mp." / hour</text>
							</div>
							<div class='tritium' >	
								<text class=hd4>".$t." <br> ".$tp." / hour</text> 
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
					<br><hr>
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
	 		  //background-color:$bgcol;
	 		  background-image: url('Images/planetlist.png');
	 		  opacity:$opac;
			}
			";
        } //end for

      
        $tmpst=$tmpst."
	.planimg
	{
      position: relative;
      display: block;
      z-index: 0;
      top: 10px;
      left: 10px;	
	  width: 48px;
	  height:48px;
	}
	.plnname
	{
      position: relative;
      display: block;
      z-index: 0;
      top: 12px;
      left: 10px;
	  width: 60px;
	  color:white;	
	}
	.ships{
	  top:2;
	  left:70;
	  width:405;
	  height:85;
	  position:absolute;
	  overflow:auto;
	 #background-color:red;	
		
	}
		.hd3
		{
  		 font-family:arial;color:darkgreen;font-size:20px;	
		 display:block;
		 text-align:center;
		}
		.hd4
		{
  		 font-family:arial;color:white;font-size:16px;	
		 text-align:center;
		}
		.gold
		{
      	 position: absolute;
      	 display: block;
         z-index: 0;
         top: 2px;
         left: 80px;
	     width: 100px;		
		}
		.metalum
		{
      	 position: absolute;
      	 display: block;
         z-index: 0;
         top: 2px;
         left: 200px;
	     width: 100px;		
		}
		.tritium
		{
      	 position: absolute;
      	 display: block;
         z-index: 0;
         top: 2px;
         left: 320px;
	     width: 100px;		
		}
  		  .shipinfo
		  {
			font-family:arial;color:#0000FF;font-size:12px;	font-weight:bold; 	    
		  }
		  .shipinfo th
		  {
			background-color:green;
			color:white;
		  }
		  

         table.shipinfo {width:365px;left:2px;}/*Setting the table width is important!*/		  
         table.shipinfo td {overflow:hidden;}/*Hide text outside the cell.*/
         table.shipinfo td:nth-of-type(1) {width:20%;text-align:left;}/*Setting the width of column 1.*/
         table.shipinfo td:nth-of-type(2) {width:10%;text-align:right;}/*Setting the width of column 2.*/
         table.shipinfo td:nth-of-type(3) {width:10%;text-align:right;}/*Setting the width of column 3.*/
         table.shipinfo td:nth-of-type(4) {width:3%;text-align:right;}/*Setting the width of column 4.*/
         table.shipinfo td:nth-of-type(5) {width:3%;text-align:right;}/*Setting the width of column 5.*/		 				         table.shipinfo td:nth-of-type(6) {width:10%;text-align:right;}/*Setting the width of column 6.*/		 					
	";

        $jscript.="	
					
		
					function checkbackground(){
						var plinf=getAjaxSessionParam('planinfo');
						console.log('PLANINFO='+plinf);
						if (plinf==1)
						  $('.planetdiv').css('background-image', 'url(Images/planetlist.png)');
						else 
  						  $('.planetdiv').css('background-image', 'url(Images/planetfleetlist.png)');						
						
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
