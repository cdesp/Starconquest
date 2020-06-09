<?php

    include_once("gamesession.php");
    include_once("myutils.php");
    include_once("shiputils.php");
    
    global $lasttime,$tmfst;

    function addtoqueue(&$tabcontent, &$tabcontentstyle, $dbarr, $qno, &$js)
    {
        global $lasttime,$tmfst;
        
        $userid=$_SESSION['id'];
        
        $sbid=$dbarr['sbid'];
        $stid=$dbarr['stid'];
        $quant=$dbarr['quantity'];
        $tmst=$dbarr['sttime'];
        $tmdur=$dbarr['durtime'];
     
        $quer = "
			SELECT * FROM `shiptypes`  where ownerid=$userid and `stid`=$stid LIMIT 1;
		" ;
        
        if (executequery($quer, $qres, $qrcnt)) {
            $dbarr=query_fetch_array($qres);
            $dbarr=getshipinfo($dbarr);
            $img=$dbarr['image'];
            $imgsml='Images/'.getsmallimage($img);
                    
            $tp=20+($qno-1)*120;
            $sname=$dbarr['stypename'];
            $g=$dbarr['ngold'];
            $m=$dbarr['nmetalum'];
            $t=$dbarr['ntritium'];
                   
            $frmnm='frm_'.$sname;
            //				   $maxships=calculatemaxships($plg,$plm,$plt,$g,$m,$t);
            //				   $defval=floor($maxships/3);
            $size=$dbarr['size'];
            $maxsize=$dbarr['maxsize'];
            $armor=$dbarr['armor'];
            $speed=calculatespeed($dbarr['engpower'], $dbarr['maxsize']);
            $scan=$dbarr['sensdist'];
            $stid=$dbarr['stid'];

            //				   $tm=calculatebuildtimeforship($stid);
            $tm=$tmdur;
            $tm1=getshiptime($tm);
            if ($qno==1) {
                $tmfst=$tmst;
                $lasttime=0;
            }
                   
            $tm2=calctimetoupgrade($tmfst+$lasttime, $tmdur);
            adddebugval('tm2', $tm2);
            $tm22=adddst($tmfst+$lasttime+$tmdur);
            adddebugval('tm22', $tm22);
            $js.="q[$qno]=$tm22;";
            $tm3=gettimetoupgrade($tm2);
            adddebugval('tm3', $tm3);
            $lasttime+=adddst($tmdur);
                   
            adddebugval('lasttime', $lasttime);
            $tabcontent.="
				     <div>
				      <img src='$imgsml' title=$sname style='left:5;top:$tp;position:absolute'/>					 							  					  <div style='left:105;top:$tp;width:200;position:absolute'>
					   <div class='stitle'> $sname	</div><br>
					    Armor $armor Speed $speed Scan $scan
					  	Resources [ g: $g m: $m t: $t ]
						Duration [$tm1] <br>
						Finished in  <div style='position:relative;top:-15;left:70' id='q_$qno'> [$tm3] </div>
					  </div>
					  <form name='$frmnm' method='post'>
  					  <div style='left:305;top:$tp;width:80;position:absolute'>
					  <input type='number' name='shipid' value=$stid hidden/>
					  <label name='quant' style='font-size:40;color:green;top:30;position:relative'>$quant</label>
					  </div>
  					  <div style='left:390;top:$tp;width:50;position:absolute'>
					    	<input type='image' name='submit' value='submit3' 
								src='Images/handup.png' border='0' 
			 					onmouseover='".'this.src="Images/handdn.png"'."'
								onmouseout='".'this.src="Images/handup.png"'."'
								style='top:10;position:relative'
							/>

					  </div>
					  </form>
				   	 </div>
				   ";
        }
    }


    function getshipqueueforplanet(&$tabcontent, &$tabcontentstyle, $pid, &$ajaxcode)
    {
        $userid=$_SESSION['id'];
        getplanetowner($pid, $pluserid, $plusername);
        if ($pluserid!=$userid) {
            $tabcontent="You dont own that planet $pluserid != $userid";
            return false;
        }


        checkshipbuild($pid);
        $quer = "
			SELECT * FROM shipbuild  where pid=$pid order by sttime;
			
		" ;
        if (executequery($quer, $qres, $qrcnt)) {
            $js='';
            $tabcontent.="<div class='planship'>";
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                addtoqueue($tabcontent, $tabcontentstyle, $dbarr, $i, $js);
            }
            $js="var qcnt=$qrcnt;\n".$js;
            $tabcontent.="</div>";
        
            $tabcontentstyle.="
		  	  .planship
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
        $stime=mtimetn();
        $jscript='var q = new Array();'.$js."var servertime=$stime;var srvdiff=$.now()/1000 - servertime;
			
     
			
				function doupdate(){
					curtime= $.now()/1000 ;
					
					for (i=1;i<qcnt+1;i++){
						nm='q_'+i;
					  tm=q[i];
					  dif=tm-curtime+srvdiff;
				//	  console.log('-------------');
				//	  console.log('servertime='+servertime);					  					  
 				//	  console.log('srvdiff='+srvdiff);					  					  
				//	  console.log('nm='+nm);
				//	  console.log('tm='+tm);
				//	  console.log('curtime='+curtime);
				//	  console.log('dif='+dif);					  					  
				//	  console.log('formdif='+formatTime(dif));					  					  
					  elm=document.getElementById(nm);					  
					  elm.innerText='['+formatTime(dif)+']';
					  	
					}
				}
			";
        
        $isajax=$_SESSION['isajax'];
        if (!$isajax) {
            addjsfunction('initform', "");
        } else {
            $ajaxcode.= getajaxjsfunction('initform', "if (typeof myint!='undefined') clearInterval(myint); myint=setInterval(doupdate,1000);//alert('hi4');").$jscript;
        }
    }
