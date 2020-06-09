<?php

    function getroutelist(&$tabcontent, &$tabcontentstyle, $usrid=null)
    {
        $dbres=getalluserroutes($usrid);
        $reccnt=query_num_rows($dbres);
        if (myisset($_SESSION['selfleet'])) {
            $fltsel=$_SESSION['selfleet'];
        }
      
        $divheight=80;

        $tabcontent="
			<div class='routes'>
			<div class='maintit'>  Route List ($reccnt)</div>
		";

        for ($i=0;$i<$reccnt;$i++) {
            $dbarr=query_fetch_array($dbres);

            $divname="divroute_$i";
            $tp=35+$i*($divheight+10);

            $rtid=$dbarr['rtid'];
            $fltid=$dbarr['fltid'];
             
            $coordx=$dbarr['curcoordx'];
            $coordy=$dbarr['curcoordy'];
            $tocoordx=$dbarr['tocoordx'];
            $tocoordy=$dbarr['tocoordy'];
            $fspeed=$dbarr['fspeed'];
            $feta=$dbarr['eta'];
            $fetas=date("d-m-Y H:i:s", $feta);
            $dur=$feta-mtimetn();
         
            $durs=getshiptime(deldst($dur));
             
            $fltres=getfleetbyid($fltid);
            $fltarr=query_fetch_array($fltres);
            $fltname=$fltarr['fltname'];
             
            if (!myisset($fltsel)) {
                $fltsel=$fltid;
            }
             
            if ((myisset($fltsel)) and($fltsel==$fltid)) {
                $issel=true;
                $_SESSION['routesel']=$rtid;
                $divname='divroute_sel';
            } else {
                $issel=false;
            }
            adddebugval('routesel', $_SESSION['routesel']);

            $tabcontent.="
			   <div class='$divname'>
			     <div class='fname'>
				   <a href='?pg=map&action=center&selfleet=$fltid'>
				    $fltname</a><br> 
				   <div style='color:#8B3626;font-size:16px;'>[$coordx:$coordy]	--> [$tocoordx:$tocoordy]</div>				
				 </div>
				 <div class ='rtinfo'>
				    Fleet speed: $fspeed<br>
					E.T.A. : $fetas <br>
				 	Duration : $durs
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
			  .divroute_sel
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
			</div'>
			
		";
        
        
        
        $tabcontentstyle.="

		  	  .fname
			  {
				font-family:arial;color:#CDCD00;font-size:20px;	font-weight:bold;
				width:30%;
				height:100%; 	        
				
				float:left;  
			  }		

			.rtinfo
			{
				top:0;
				width:70%;
				height:100%;
				font-family:arial;color:yellow;font-size:14px;	 	  
  			    float:right;  
				overflow:auto;

			}
		  .maintit
		  {
			left:0;
			width:100%;
			text-align:center;
			position: absolute;
			font-family:arial;color:green;font-size:24px;	font-weight:bold; 	      
		  }
		  .routes
		  {
 			font-family:arial;color:#8B3626;font-size:12px;	 	  
			position: absolute;
			display: block;      	
			left:5px;	
			top:5px;
			width: 98%;
			height: 98%;
			overflow:auto;
		  }

		  .stitle
		  {
			font-family:arial;color:#0000FF;font-size:16px;	font-weight:bold; 	    
		  }
		 ";
    }


    $_SESSION['showroutes']=1;
