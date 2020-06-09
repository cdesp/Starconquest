<?php

    include_once("shiputils.php");
    include_once("myutils.php");

    function getfleetlist(&$tabcontent, &$tabcontentstyle, &$jscript, $usrid=null)
    {
        $dbres=getalluserfleets($usrid);
        $reccnt=query_num_rows($dbres);
        if (myisset($_SESSION['selfleet'])) {
            $fltsel=$_SESSION['selfleet'];
        }
      
        $divheight=80;

        $tabcontent="
			<div class='fleets'>
			<div class='maintit'>  Fleet List</div>
		";

        for ($i=0;$i<$reccnt;$i++) {
            $dbarr=query_fetch_array($dbres);

            $divname="divfleet_$i";
            $tp=35+$i*($divheight+10);
             
            $fltid=$dbarr['fltid'];
            $fltname=$dbarr['fltname'];
            $coordx=$dbarr['coordx'];
            $coordy=$dbarr['coordy'];
             
            if ((myisset($fltsel)) and($fltsel==$fltid)) {
                $issel=true;
                $divname='divfleet_sel';
                $rtid=getfleetonroute($fltid);
               
                $_SESSION['routesel']=$rtid;
            } else {
                $issel=false;
            }

            //			adddebugval('divname',$divname);
            $tabcontent.="
			   <div class='$divname' id='$divname'>
			     <div class='fname'>
				   <a href='?pg=map&action=center&selfleet=$fltid'>
				    $fltname</a><br> 
				   [$coordx:$coordy]					
				 </div>
				 <div class='ships'>
				 
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
              
            $shpres=getshipsoffleet($fltid);
            $shpcnt=query_num_rows($shpres);
              
              

            for ($si=0;$si<$shpcnt;$si++) {
                $shparr=query_fetch_array($shpres);
                $shparr=getshipinfo($shparr);
                $shpname=$shparr['stypename'];
                $maxsize=getformatednumber($shparr['maxsize']);
                $speed=$shparr['speed'];
                $quant=$shparr['quantity'];
                $armor=$shparr['armor'];
                  
                $tabcontent.=" 
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
            $tabcontent.="  
			   </tbody>
			  </table>		
				 
				 
				 </div>

			   
			   
			   
			   </div>
			";
            
            if (!$issel) {
                $tabcontentstyle.="
			  .$divname
			  {
				top:$tp;
				left:0;
				width:99%;
				height:$divheight;
				background-color:#43C3C3;  
				position:absolute;  
				
			  }
			 ";
            } else {
                $tabcontentstyle.="
			  .divfleet_sel
			  {
				top:$tp;
				left:0;
				width:99%;
				height:$divheight;
				background-color:#4363C3;  
				position:absolute;  				  
				
			  }
		     ";
            }
        }

        $tabcontent.="
			</div>
			
		";
        
        
        
        $tabcontentstyle.="
			.ships
			{
				top:0;
				width:78%;
				height:100%;
				background-color:#00C300;  
  			    float:right;  
				overflow:auto;
			}
			.fname
			  {
				font-family:arial;color:green;font-size:24px;	font-weight:bold;
				width:20%;
				height:100%; 	        
				
				float:left;  
			  }		
		  .maintit
		  {
			left:0;
			width:100%;
			text-align:center;
			position: absolute;
			font-family:arial;color:green;font-size:24px;	font-weight:bold; 	      
		  }
		  .fleets
		  {
 			font-family:arial;color:#CCCC00;font-size:12px;	 	  
			position: absolute;
			display: block;      	
			left:5px;	
			top:5px;
			width: 99%;
			height: 98%;
			overflow:auto;
		  }
		  .stitle
		  {
			font-family:arial;color:#0000FF;font-size:16px;	font-weight:bold; 	    
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
        
        
        $jscript="	
					function initform(){	
					  scrollToView('divfleet_sel');						
					}
				";
    }
