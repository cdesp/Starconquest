<?php

   include_once "common.php";
   include_once "mytabset.php";
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once("battleutils.php");


    function checkparams()
    {
    }
    
    function getbattledesign(&$tmppl, &$tmpst)
    {
        global $bgwidth,$bgheight,$bgoffsety;
        
    
        $tmppl.= "
			  <div class='mainbattlegrid' name='mainbattlegrid'>
			  </div>
			  <div class='mainbattleround' name='mainbattleround'>
			  </div>
			  <div class='battlecontrols' name='battlecontrols'>
			  </div>

		";
        
        $ctrlh=145;
        $tmpst.="
			.mainbattlegrid
			{
			 top:0;
			 position:absolute;	
			 width:$bgwidth;
			 height:$bgheight;	
			}
			.mainbattleround
			{
			 top:0;
			 position:absolute;	
			 width:$bgwidth;
			 height:$bgheight;	
			}
			.battlecontrols
			{
			 top:$bgheight;
			 position:absolute;	
			 width:$bgwidth;
			 height:$ctrlh;	
			 background-color:green;
			}

		";
    }
    
    
    function getbattlegrid(&$tmppl, &$tmpst)
    {
        $tmppl.= "
              <img class='battleimg' src='Core/battlegrid.php'>
	    ";
    
        $tmpst.="
			.battleimg
			{
		      position: absolute;
		      display: block;
		      z-index: 1;
		      top: -0px;
		      left: 00px;	
			}
		";
    }

    function getbattlelist(&$tabcontent, &$tabcontentstyle, &$ajaxcode)
    {
        $batres=getuserbattles($batcnt);
        $tabcontent.="<div class='batdiv'>";
        for ($i=0;$i<$batcnt;$i++) {
            $batarr=query_fetch_array($batres);
            $batid=$batarr['batid'];
            $batfin=$batarr['finished']=='Y';
            if ($i==0 and !myisset($_SESSION['selbattle'])) {
                $_SESSION['selbattle']=$batid;
            }
            $scx=$batarr['bcoordx'];
            $scy=$batarr['bcoordy'];
            $pid=getplanetfromcoords($scx, $scy);
            $batname="Battle at sector [$scx:$scy]";
            $sttm=$batarr['bsttime'];
            $tms=date("d-m-Y H:i:s", $sttm);
            $atkfleets=getbattlefleetnumber($batid, "Y");
            $deffleets=getbattlefleetnumber($batid, "N");
            if ($batfin) {
                $bfs="(FINISHED)";
            } else {
                $bfs="";
            }
           
            if ($pid==null) {
                $ss='the sector';
            } else {
                $pname=getplanetname($pid);
                $ss="planet $pname";
            }
                  
            $top=10+$i*65;
            $tabcontent.="<div class='batdivin' name='batdiv_$batid' id='batdiv_$batid'  style='top:$top;'>
		     	 <div id='fn_$i' class='fname clkbat' data-batid=$batid>				   
				    <strong>$batname</strong> $bfs
				 </div>
				 <div class='time'>
				   Started at $tms
				 </div>
				 <div>
				   $atkfleets Fleet attacking and $deffleets defending $ss 
				 </div>
				</div>
		 
		 
		 
		 ";
        }
        
        $tabcontent.="</div>";
        $tabcontentstyle="
				.batdiv
				{
				  width:410;
				  height:530;
				 #background-color:red;
				  overflow:auto;
					
				}
				.batdivin
				{
				  position:absolute;
				  width:360;
				  height:60;				  
				  left:50;
				  #background-color:blue;
				}
				.fname
				{
				  font-size:18;		
				}
				.time
				{
				  top:0;
				  width:150;	
					
				}
				.clkbat
				{
				  cursor: pointer;	
				}
		";
        
        $isajax=$_SESSION['isajax'];
        if (!$isajax) {
            addjsfunction('initform', "");
        } else {
            $ajaxcode.= getajaxjsfunction('initform', getbattlejavascript());
        }
    }
    
    function getbattlejavascript()
    {
        $jscript="
	  	selbatid=null;
	  
	  	//alert('initializing battle');
		$('.fname').on('click', function(e){					
					batid=$(this).data('batid');
					newbattle(batid);						
				})
		
	  ";
        
        return $jscript;
    }


    function ajaxgetparam()
    {
        global $bgwidth,$bgheight,$bgoffsetx,$bgoffsety,$bgquadrh,$bgquadrv,$redzonex,$redzonewidth,$quadsizex,$quadsizey;
        
        if (myisset(filter_input(INPUT_GET, 'param'))) {
            switch (filter_input(INPUT_GET, 'param')) {
            
            case 'bgoffsetx':
                return $bgoffsetx;
                break;
            case 'bgoffsety':
                return $bgoffsety;
                break;
            case 'bgwidth':
                return $bgwidth;
                break;
            case 'bgheight':
                return $bgheight;
                break;
            case 'quadsizex':
                return $quadsizex;
                break;
            case 'quadsizey':
                return $quadsizey;
                break;
            case 'bgquadrh':
                return $bgquadrh;
                break;
            case 'bgquadrv':
                return $bgquadrv;
                break;
            
            
            
            
          }
        }
    }

    function getrealbattlecoords($scx, $scy, &$rcx, &$rcy)
    {
        global $bgoffsetx,$bgoffsety,$quadsizex,$quadsizey;
        $scx--;
        $scy--;
        $rcx=$bgoffsetx+$scx*$quadsizex;
        $rcy=$bgoffsety+$scy*$quadsizey;
    }
    
    function getimageforship($stid)
    {
        $qr="select image from shiptypes,x_hulls where stid=$stid and xhullid=hullid";
        if (executequery($qr, $qrres, $qrcnt) and $qrcnt>0) {
            $dbarr=query_fetch_array($qrres);
            $img=$dbarr['image'];
            return $img;
        } else {
            return 'noship';
        }
    }

    function putshipongrid($shparr, &$tmpct, &$tmpst, $isatk)
    {
        global $quadsizex,$quadsizey;

        $quant=$shparr['quantity'];
        $killed=$shparr['killed'];
        $scx=$shparr['scoordx'];
        $scy=$shparr['scoordy'];
        $stid=$shparr['stid'];
        $fltid=$shparr['fltid'];
        getrealbattlecoords($scx, $scy, $rcx, $rcy);
        $img=getimageforship($stid);
        $nimg=getsmallimage($img);
        $imgsml='Images/'.$nimg;
        $newquant=($quant-$killed)*1;
        $tit="$newquant [$scx:$scy]";
        $qszx=$quadsizex-4;
        $qszy=$quadsizey-4;
      
        if ($isatk) {
            $col='aqua';
        } else {
            $col='#FFC125';
        }
      
        $tmpct.= "		
	      <div class='ships' id='ships_$fltid.$stid' data-fltid=$fltid data-stid=$stid style='position:absolute;left:$rcx;top:$rcy;z-index:2' title=$tit>
	         <img src='$imgsml'   height='$qszx' width='$qszy'>
			 <div class='shpnum' style='color:$col'><strong>$newquant</strong></div>
		  </div>
		  
	   ";
    }

    function putfleetongrid($batid, $atkarr, &$tmpct, &$tmpst, $isatk, $round, &$stidarr, &$jsdataset, &$jsfleets)
    {
        global $quadsizex,$quadsizey;



        $fltid=$atkarr['fltid'];

        $ownerid=$atkarr['ownerid'];
        $scx=$atkarr['scoordx'];
        $scy=$atkarr['scoordy'];
                 
        $shpsres=getbattlefleetships($batid, $fltid, $round, $shpcnt);
        $img=$round;
        $stidarr[0]=$shpcnt;
        $quant=0;
        $oldsize=0;
        $speed=9999;
        $n=count($jsdataset);
        for ($j=0;$j<$shpcnt;$j++) {
            $shparr=query_fetch_array($shpsres);
            $killed=$shparr['killed'];
            $quant+=$shparr['quantity']-$killed;
            $stid=$shparr['stid'];
            // $shparr=getshipinfo($shparr);//todo:get the maxsize of shiptype
            $stidarr[$stid]=$stid;
            $size=0;//$shparr['maxsize'];
           $speed=0;//min($shparr['maxsize'],$speed);
           $jsdataset[$n++]=$shparr;
            if ($size>=$oldsize) { //show the biggest size ship image
                $img=getimageforship($stid);
                $oldsize=$size;
            }
        }
       
        getrealbattlecoords($scx, $scy, $rcx, $rcy);
        $nimg=getsmallimage($img);
        $imgsml='Images/'.$nimg;
        $atkarr['image']=$imgsml;
        //	   if ($jsfleets==null)
        //	      $n=0;
        //	   else
        //	      $n=count($jsfleets);
        $jsfleets[$fltid]=$atkarr;
       
       
        $newquant=$quant;
        $tit="'$newquant ($shpcnt shiptypes) \n [$scx:$scy][$rcx,$rcy]'";

        $qszx=$quadsizex-4;
        $qszy=$quadsizey-4;
      
        if ($isatk) {
            $col='aqua';
        } else {
            $col='#FFC125';
        }
      
        $tmpct.= "		
	      <div class='ships' id='ships_$fltid' data-fltid=$fltid  style='position:absolute;left:$rcx;top:$rcy;z-index:4' title=$tit>
	         <img src='$imgsml'   height='$qszx' width='$qszy'>
			 <div class='shpnum' style='color:$col'><strong>$newquant</strong></div>
		  </div>
		  
	   ";
    }


//arr = [{key: key1, value: value1}, {key: key2, value: value2}];

    function getbattleround(&$tmpct, &$tmpst, &$jscr, $batid, $round)
    {
        $jsdataset=null;
        $jsfleets=null;
        $stidarr=null;
        
        $tmpct.= " 
 			<div class='battleroundin' name='battleroundin'>
		";
        
        $n=0;
        $ftp="Y";
        $nn=0;
        do {
            $atkres=getbattlefleets($batid, $round, $ftp, $atkcnt, true);
            for ($i=0;$i<$atkcnt;$i++) {
                $atkarr=query_fetch_array($atkres);
                // $fltid=$atkarr['fltid'];
                // $ownerid=$atkarr['ownerid'];
                //$shpsres=getbattlefleetships($batid,$fltid,$round,$shpcnt);
                putfleetongrid($batid, $atkarr, $tmpct, $tmpst, $n==0, $round, $stidarr, $jsdataset, $jsfleets);
                //   for ($j=0;$j<$shpcnt;$j++){
    //		   $shparr=query_fetch_array($shpsres);
    //		   $stid=$shparr['stid'];
    //		   $stidarr[$stid]=$stid;
    //		   $shparr['attacker']=$ftp;
    //		   $shparr['ownerid']=$ownerid;
    //		   $jsdataset[$nn++]=$shparr;
    //		   putshipongrid($shparr,$tmpct,$tmpst,$n==0);
    //	   }
            }
            $ftp="N";
        } while (++$n<2);//do
        
        $tmpct.= "</div>";
        
        $tmpst.="
		   .battleroundin
		   {
			position:relative;
			top:0;   
			   
			   
		   }
			.shpnum
			{
			  position:absolute;left:10;top:17;	
			  font-size:11;
			  text-align:right;
			  width:18;
			}
		";

        //add shiptype info
        foreach ($stidarr as $key => $value) {
            $shparr=getallshipinfo($key);
            $stpdataset[$key]=$shparr;
        }

        $jsflt_array = json_encode($jsfleets);
        $js_array = json_encode($jsdataset);
        $stp_array = json_encode($stpdataset);
        $jscr= "".
                " var fleetsarr = ". $jsflt_array . ";\n  ".
                " var shipsarr = ". $js_array . ";\n  ".
               " var stypearr = ". $stp_array . ";\n  "
        ;
    }
    
    function getbattlecontrols(&$tmpct, &$tmpst, $rnd, $maxround)
    {
        $tmpct.="<div class='roundcontrols'>
			  
			      <div class='tpstr tape' id='tpstr' data-action='start' title='to round 1'>
	         		 <img src='Images/tp_str.png' onmouseout='sethoverimageout(this,\"Images/tp_str\")' onmouseover='this.src=\"Images/tp_str_ov.png\"'  height='55' width='55'>
				  </div>
			      <div class='tppre tape' id='tppre' data-action='previous' title='to previous round'>
	         		 <img src='Images/tp_pre.png' onmouseout='sethoverimageout(this,\"Images/tp_pre\")' onmouseover='this.src=\"Images/tp_pre_ov.png\"' height='55' width='55'>
				  </div>
				  <div class='tpinfo' id='tpinfo'>
				    <div id='tpinfotext' style='position: absolute; top: 30%;width:100%;display: table-cell; vertical-align: middle;					text-align:center;'>
				      Round $rnd of $maxround			
					 </div> 
				  </div>
			      <div class='tpnxt tape' id='tpnxt' data-action='next' title='to next round'>
	         		 <img src='Images/tp_nxt.png' onmouseout='sethoverimageout(this,\"Images/tp_nxt\")' onmouseover='this.src=\"Images/tp_nxt_ov.png\"' height='55' width='55'>
				  </div>
			      <div class='tpend tape' id='tpend' data-action='end' title='to last round'>
	         		 <img src='Images/tp_end.png'  onmouseout='sethoverimageout(this,\"Images/tp_end\")' onmouseover='this.src=\"Images/tp_end_ov.png\"' height='55' width='55'>
				  </div>
			      <div class='movcmd tape' id='movcmd' data-action='move' title='move ships'>
	         		 <img id='imgmovcmd' src='Images/batmovcmd.png'  onmouseout='sethoverimageout(this,\"Images/batmovcmd\")' onmouseover='this.src=\"Images/batmovcmd_ov.png\"' height='55' width='55'>
				  </div>
		  
		  
		  
		  <div>
		";
        
        
        $tmpst.="
				.roundcontrols
				{
					position:absolute;
			      left:60;
				  height:55;
				  width:420;
				  background-color:	#EE5C42;
				}
				.tpstr
				{
				  	position:absolute;	
					left:60;
					top:0;
				  z-index:2;
				}
				.tppre
				{
				  	position:absolute;	
					left:110;
					top:0;
				  z-index:2;					
				}
				.tpnxt
				{
				  	position:absolute;	
					left:320;
					top:0;
				  z-index:2;
				}
				.tpend
				{
				  	position:absolute;	
					left:370;
					top:0;
				  z-index:2;					
				}
				.movcmd
				{				
				  	position:absolute;	
					left:0;
					top:0;
				  z-index:2;					
					
				}
				.tpinfo
				{
				  	position:absolute;	
					left:170;
					top:0;
					width:150;
					height:55;

					
				 font-size:16;
				 color:white;
				 #background-color:grey;	
				}
		
		";
    }
    
        //this is called by client through ajax return the tab info
    function battleinforequested($info)
    {
        db_connect();
        activityoccur();
        $ajaxcode='';
        
        switch ($info) {
            
        case 0:
        
                //map
            
                   $cont='';$contstyle='';
                   getbattlegrid($cont, $contstyle);
            
                   $sntback= "<style type='text/css'>".$contstyle."</style>".$cont;
                   $retarr['content']=$sntback;
            break;
            
        case 1:
            getbattlelist($tabcontent, $tabcontentstyle, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        
        case 20: //get html for battle round=round
              if (myisset(filter_input(INPUT_GET, 'round'))) {
                  $rnd=filter_input(INPUT_GET, 'round');
                  $batid=filter_input(INPUT_GET, 'batid');
                  getbattleround($cont, $contstyle, $ajaxcode, $batid, $rnd);
                  $sntback= "<style type='text/css'>".$contstyle."</style>".$cont;
                  $retarr['content']=$sntback;
              }
        
            break;
        case 21: //get html for battle controls
                
                 if (myisset(filter_input(INPUT_GET, 'round'))) {
                     $rnd=filter_input(INPUT_GET, 'round');
                     $batid=filter_input(INPUT_GET, 'batid');
                     $maxround=getbattlenextround($batid);
                 } else {
                     $rnd=0;
                     $maxround=99;
                 }
                 $retarr['maxround']=$maxround;
                 getbattlecontrols($cont, $contstyle, $rnd, $maxround);
                 $sntback= "<style type='text/css'>".$contstyle."</style>".$cont;
                 $retarr['content']=$sntback;
            break;
            
        case 50: // set ship movement
                if (myisset(filter_input(INPUT_GET, 'fltid'))) {
                    $batid=filter_input(INPUT_GET, 'batid');
                    $fltid=filter_input(INPUT_GET, 'fltid');
                    //$stid=filter_input(INPUT_GET, 'stid');
                    $x=filter_input(INPUT_GET, 'x');
                    $y=filter_input(INPUT_GET, 'y');
                    $retarr['content']="ship $fltid will move to [$x:$y]\n ship command OK";
                    $retarr['content'].=setbattlefleettomove($batid, $fltid, $x, $y);
                }
        
            break;
            
        case 98:$retarr['param']=ajaxgetparam();break;
        }
        $retarr['scriptcode']=$ajaxcode;
        getdebugdata($retarr);
        
        echo json_encode($retarr);
    }


    function battlemain()
    {
        global $selplanet,$bgwidth;
        
        $tabarr[0]='BATTLES';
        
        $maintab="battabmain";
        
        
        $tabcontent="<div class='$maintab defmaintab' name='$maintab'> </div>";
        $tabcontentstyle="
		 .$maintab
		 {
                   width:410;
		 }
		";
        
        $lft=$bgwidth+5;
        createtab2($maintab, $tabarr, $lft, 140, 1020-$lft, 622, $tabcontent, 'tabpressed');

        addoutput("", $tabcontentstyle);
        
            
        //tab pressed so get data through ajax
        $jscript="
		
			
		$(function(){
				  // Functionality starts here
				$('.clkbat').on('click', function(e){	
					alert('ok')				
					batid=$(this).data('batid');
					newbattle(batid);						
				})

		});		
			

		   function tabpressed(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			    info=1;
			 break;
			}
			
				obj=getAjaxInfo(info,'battle','p=0','myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
				

			 //  if (obj.scriptcode!='') 
  			  //   addScriptToPage(obj.scriptcode,'myscript');
				 
			 // call a default function if exists
				if (typeof initform == 'function') { 
				  initform(); 
				}//	else alert('no func');		 
			 
		   }

			
		
		";
        
        addjscript($jscript);
        
        $incjsf=getjsincludefile('jscript/common.js');
        addincludefile($incjsf);
        $incjsf=getjsincludefile('jscript/battle.js');
        addincludefile($incjsf);
    }


    function init_page()
    {
        checkparams();
        
        $cont="<div class='divbattlearea' name='divbattlearea' '>";
        $contstyle="
		      .divbattlearea
			  {
				position:absolute;
				left:0;
				top:128;
				width:600;
				height:600;
			  }
		   ";


        //get main design for battle
        getbattledesign($cont, $contstyle);
        $cont.="<div name='divmesg'>PLEASE SELECT A BATTLE</div></div>";
        addoutput($cont, $contstyle);

        battlemain();
    }

    if (myisset(filter_input(INPUT_GET, 'info'))) {
        $_SESSION['isajax']=true;
        battleinforequested(filter_input(INPUT_GET, 'info'));
        $_SESSION['isajax']=false;
    } else {
        $_SESSION['isajax']=false;
    }
