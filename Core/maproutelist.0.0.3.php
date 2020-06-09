<?php

    function getroutelist(&$tabcontent, &$tabcontentstyle, &$jscript, $usrid=null)
    {
        $dbres=getalluserroutes($usrid);
        $reccnt=query_num_rows($dbres);
        //	  adddebugval('routecnt',$reccnt);
      
        if (myisset($_SESSION['selfleet'])) {
            $fltsel=$_SESSION['selfleet'];
        }
      
        $divheight=80;

        $tabcontent="
			<div class='divroutelist'>
			<div class='maintit'>  Route List ($reccnt)</div>
		";
        $jscript.="var rt = new Array();var rtcnt=$reccnt;";
        for ($i=0;$i<$reccnt;$i++) {
            $dbarr=query_fetch_array($dbres);

             
            $tp=35+$i*($divheight+10);

            $rtid=$dbarr['rtid'];
            $fltid=$dbarr['fltid'];
            $divname="divroute_$fltid";
            $coordx=$dbarr['curcoordx'];
            $coordy=$dbarr['curcoordy'];
            $tocoordx=$dbarr['tocoordx'];
            $tocoordy=$dbarr['tocoordy'];
            $fspeed=$dbarr['fspeed'];
            $feta=$dbarr['eta'];
            $fetas=date("d-m-Y H:i:s", $feta);
            $jscript.="rt[$i]=$feta;";
            $dur=$feta-mtimetn();
         
            $durs=getshiptime(deldst($dur));
             
            $fltres=getfleetbyid($fltid);
            $fltarr=query_fetch_array($fltres);
            $fltname=$fltarr['fltname'];
             
            if (!myisset($fltsel)) {
                $fltsel=$fltid;
            }
            $col='#43C3C3';
            if ((myisset($fltsel)) and($fltsel==$fltid)) {
                $issel=true;
                $_SESSION['routesel']=$rtid;
                $col='#4363C3';
                // $divname.=' divroute_sel';
            } else {
                $issel=false;
            }
            adddebugval('routesel', $_SESSION['routesel']);

            $tabcontent.="
			   <div class='$divname'>
			     <div class='fname'>
				   <a href='javascript:selectFleet($fltid);'>
				    $fltname</a><br> 
				   <div style='color:#8B3626;font-size:16px;'>[$coordx:$coordy]	--> [$tocoordx:$tocoordy]</div>				
				 </div>
				 <div class ='rtinfo'>
				    Fleet speed: $fspeed<br>
					E.T.A. : $fetas <br>
				 	Duration :  <label id='rt_$i'> $durs  </label>
				 </div>
				</div>				 
			  ";


            $tabcontentstyle.="
			  .$divname
			  {
				top:$tp;
				left:0;
				width:99%;
				height:$divheight;
				background-color:$col;  
				position:absolute;  
			  }
			 ";
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
		  .divroutelist
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
        $stime=mtimetn();
        $jscript.="	
					var servertime=$stime;var srvdiff=$.now()/1000 - servertime;
				
					function doupdate(){
						curtime= $.now()/1000 ;
						for (i=0;i<rtcnt;i++){
							nm='rt_'+i;
						  eta=rt[i];
						  dif=eta-curtime+srvdiff;
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
							$('.divroute_'+selfleet).css('background-color','#43C3C3');
						$('.divroute_'+fltid).css('background-color','#4363C3');			
						selfleet=fltid;
					//	console.log('sf='+selfleet);
						refreshMap();
						
						//tabpressed(null,selectedTab);//reload tab
					}
					
				";
    }


    $_SESSION['showroutes']=1;
