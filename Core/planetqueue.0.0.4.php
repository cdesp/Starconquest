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
        $buildsofar=$dbarr['buildsofar'];
        $shtime    =$dbarr['shiptime'];
        $q=$quant-$buildsofar;
     
        $quer = "
			SELECT * FROM `shiptypes`  where ownerid=$userid and `stid`=$stid LIMIT 1;
		" ;
        
        if (executequery($quer, $qres, $qrcnt)) {
            $dbarr=query_fetch_array($qres);
            $dbarr=getshipinfo($dbarr);
            $img=$dbarr['image'];
            $imgsml='Images/'.getsmallimage($img);
                    
            $tp=1+($qno-1)*110;
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
            $divclass="pqb_$stid-$qno";

            adddebugval('tmdur', $tmdur);
            $tm1=getshiptime($tmdur);//in string
                   
            //  $tm2=calctimetoupgrade($tmst,$tmdur);
            $tm2=$tmst+$tmdur;
            adddebugval('tm2', $tm2);
                   
            $js.="q[$qno]=$tm2;sp[$qno]=$shtime;";
            //$tm3=gettimetoupgrade($tm2);
            $tm3=getshiptime($tm2-mtimetn());
            adddebugval('tm3', $tm3);
                   
            $tabcontent.="<div class='planqueuebuilt $divclass'>
                                            <div class='pbsimg'>
                                                <img src='$imgsml' title=$sname />"
                                           . "</div>"
                                           . "
					   <div class='pqbtitle'> $sname </div>
                                           <label class='pbsarmor'> $armor </label>
                                           <label class='pbsspeed'> $speed </label>
                                           <label class='pbsscan'> $scan </label>					    
                                           <label class='pbsgold'> $g </label>
                                           <label class='pbsmetal'> $m </label>
                                           <label class='pbstrit'> $t </label>					                                                                                          
  					   <label class='pqbquant'> $quant </label>
					  <div class='pqbtimeleft' id='q_$qno'> [$tm3] </div>
					  
				   </div>
				   ";
            $tabcontentstyle.=".$divclass{top:$tp}";
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
        addbottomoutput($quer);
        //adddebug($quer);
        if (executequery($quer, $qres, $qrcnt)) {
            $js='';
            $tabcontent.="<div class='planqueuetit'></div>"
                                   . "<div class='planship planshipscroll'>";
                        
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                addtoqueue($tabcontent, $tabcontentstyle, $dbarr, $i, $js);
            }
            // adddebugval("QRcnt",$qrcnt);
            $js="var qcnt=$qrcnt;\n".$js;
            $tabcontent.="</div>";
        
            $tabcontentstyle.="";
        }
        $stime=mtimetn();
        $jscript='var q = new Array();var sp = new Array();'.$js."var servertime=$stime;var srvdiff=$.now()/1000 - servertime;var lastrefr=$.now()/1000;
			
     
	 			function dorefreshqueue(){
	 			  console.log('ref queue');
					clearInterval(myint);
					tabpressed(null,info);
					lastrefr=$.now()/1000;
				}
			
				function doupdate(){
					curtime= $.now()/1000 ;
					
					for (i=1;i<qcnt+1;i++){
						nm='q_'+i;
					  tm=q[i];
					  dif=tm-curtime+srvdiff;
					  dif2=sp[1]-(curtime-lastrefr);
					  

					 // console.log('-------------');
					  //console.log('servertime='+servertime);					  					  
 					  //console.log('srvdiff='+srvdiff);					  					  
					  //console.log('nm='+nm);
					  //console.log('tm='+tm);
				    //console.log('curtime='+curtime);
					  //console.log('dif='+dif);					  					  
					  //console.log('formdif='+formatTime(dif));					  					  
					  //console.log('time to refr='+(curtime-lastrefr));	
					  
					  elm=document.getElementById(nm);
					  if (typeof elm !='undefined' && elm!=null)	
					  {
					    elm.innerText='['+formatTime(dif)+']';
					    if ((dif<0) || (dif2<0)) dorefreshqueue(); 					  	
					  }  
  					
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
