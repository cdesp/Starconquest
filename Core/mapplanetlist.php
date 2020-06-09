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
          
        if (!myisset($_SESSION['planinfo'])) {
            $_SESSION['planinfo']=1;
        }
        $infotp=$_SESSION['planinfo'];

        $divheight=95;

        $tmppl="
		  <div class='infoimages'>
			<a href='?pg=map&planinfo=1'><img class='infoimg1' src='Images/info1.png' title='Production'> 
			</a>				
			<a href='?pg=map&planinfo=2'><img class='infoimg2' src='Images/info2.png' title='Ships'> 
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
				  background-color:#6495ED;
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
 		  		   font-family:arial;color:darkgreen;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:85;
				   top:5px;
				}
				.lhd2
				{
					position: absolute;
 		  		   font-family:arial;color:darkgreen;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:190;					
				   top:5px;
				}
				.lhd3
				{
					position: absolute;
 		  		   font-family:arial;color:darkgreen;font-size:20px;	
		           display:block;
		           text-align:center;
				   left:320;					
				   top:5px;
				}
				

		";
        
        
        
        for ($i=0;$i<$reccnt;$i++) {
            $dbarray=query_fetch_array($dbres);
            if ($dbarray==false) {
                break;
            }
            $pid=$dbarray['pid'];
            $sclass="";
            if ($selplanet==$pid) {
                $bgcol='#FF82AB';
                $sclass="selplanet_id";
            } else {
                $bgcol='#6495ED';
            }
          
          
          
            $gp=$dbarray['goldprod'];
            $mp=$dbarray['metalumprod'];
            $tp=$dbarray['tritiumprod'];
            getplanethourprod($pid, $gp, $mp, $tp);
            $g=getformatednumber($dbarray['gold']);
            $m=getformatednumber($dbarray['metalum']);
            $t=getformatednumber($dbarray['tritium']);

            $tmppl.="
		 <div class='planetdiv_$i' id='$sclass'>	
	     	<a href='?pg=map&action=center&selplanet=".$pid."'><img class='planimg' src='Images/".$dbarray['imagename']."48.png'> 
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
			.planetdiv_$i
			{
   			  position: absolute;
   			  display: block;
      			z-index: 0;
      		  top: ".$divtp."px;
      		  left: 2px;	
	  		  width: 480px;
	  		  height: ".$divheight."px;
	 		  background-color:$bgcol;
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
      left: 1px;
	  width: 60px;	
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
  		 font-family:arial;color:blue;font-size:16px;	
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
					function initform(){	
 					   scrollToView('selplanet_id');
						
					}
				";


        $tmppl.="</div>";

        $plnout=$tmppl;
        $plnstl=$tmpst;
    }


    function getplanetlist2(&$plnout, &$plnstl, $usrid=null)
    {
        $dbres=getalluserplanets($usrid);
        $reccnt=query_num_rows($dbres);
        if ($reccnt>0) {
            $maxpg= floor($reccnt / 5);
            if ($reccnt % 5 > 0) {
                $maxpg++;
            }
            if (myisset($_SESSION['curplpage'])) {
                $cp=  $_SESSION['curplpage'];
            } else {
                $cp=1;
                $_SESSION['curplpage']=$cp;
            }
            $_SESSION['maxplpage']=$maxpg;
          
          
            $tmppl= "
  		<div class='planlist'>	
		";
            $tmpst=" 
	.planlist
	{
      position: absolute;
      display: block;
      z-index: -1;
      top: 5px;
      left: 5px;	
	}
		";
            for ($i=0;$i<($cp-1)*5;$i++) {
                $dbarray=query_fetch_array($dbres);//skip recs
            }
            for ($i=$cp;$i<$cp+5;$i++) {
                $dbarray=query_fetch_array($dbres);
                if ($dbarray==false) {
                    break;
                }
                $tmppl=$tmppl. "
		 <div class='planetdiv_$i'>	
	     	<a href='?pg=map&action=center&selplanet=".$dbarray['pid']."'><img class='planimg' src='Images/".$dbarray['imagename']."48.png'> 
			<div class='plnname' >".$dbarray['name']."</div><br></a>
		<div class='gold' >	
		<label class='hd3'>GOLD <br><br><text class=hd4>".$dbarray['gold']." <br> ".$dbarray['goldprod']." / hour</text> </label>
		</div>
		<div class='metalum' >	
		<label class='hd3'>METALUM <br><br><text class=hd4>".$dbarray['metalum']." <br> ".$dbarray['metalumprod']." / hour</text> </label>
		</div>
		<div class='tritium' >	
		<label class='hd3'>TRITIUM <br><br><text class=hd4>".$dbarray['tritium']." <br> ".$dbarray['tritiumprod']." / hour</text> </label>
		</div>

			<hr>
		</div>	
		
		
	 ";

                $tp=2+($i-$cp)*100;
                $tmpst=$tmpst."
	.planetdiv_$i
	{
      position: absolute;
      display: block;
      z-index: 0;
      top: ".$tp."px;
      left: 2px;	
	  width: 480px;
	  height: 90px;
	}
		";
            }
        }
      
      
      
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
      left: 1px;
	  width: 60px;	
	}
		.hd3
		{
  		 font-family:arial;color:darkgreen;font-size:20px;	
		 display:block;
		 text-align:center;
		}
		.hd4
		{
  		 font-family:arial;color:blue;font-size:16px;	
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
	
	";


        $tmppl=$tmppl. "
  <div class='planlistmenu'>
     <a href='index.php?pg=map&action=plistle' ><img class='plistle' src='Images/arrowle.png'> </a>
	 <label class='pglbl'><b>Page $cp of $maxpg</b></label>
     <a href='index.php?pg=map&action=plistri' ><img class='plistri' src='Images/arrowri.png'> </a>
	</div>
	";

        $tmpst=$tmpst."
	.planlistmenu
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: 505px;
      left: 100px;		
	}
	.plistle
	{
      position: absolute;
      display: block;
      z-index: 3;
      top: 0px;
      left: 0px;	
	}
	.plistri
	{
      position: absolute;
      display: block;
      z-index: 3;
      top: 0px;
      left: 180px;	
	}
	.pglbl
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: 5px;
      left: 0px;	
	  width: 200px;		
	  font-family:arial;color:DarkSlateGray  ;font-size:16px;
  	  text-align:center;	
	}
	";

        $tmppl=$tmppl."</div>";

        $plnout=$tmppl;
        $plnstl=$tmpst;
    }
