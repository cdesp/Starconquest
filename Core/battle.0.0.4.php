<?php

include_once "common.php";
include_once "mytabset.php";
include_once("galaxyutils.php");
include_once("myutils.php");
include_once("battleutils.php");
include_once("srvendturn.php");

function checkparams()
{
}

function getbattledesign(&$tmppl, &$tmpst)
{
    global $bgwidth, $bgheight, $bgoffsety;


    $tmppl .= "
			  <div class='mainbattlegrid' name='mainbattlegrid'>
			  </div>
			  <div class='mainbattleround' name='mainbattleround'>
			  </div>
			  <div class='battlecontrols myemboss' name='battlecontrols'>
			  </div>
                          

		";

    $ctrlh = 145;
    $ctrlw = $bgwidth - 10;
    $tmpst .= "
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
                         margin-top:3px;
			 top:$bgheight;
			 position:absolute;
			 width:$ctrlw!important;
			 height:$ctrlh!important;			 
                         /*background-image: url('Images/Battle_report.png');*/
			}
		";
}

function getbattlegrid(&$tmppl, &$tmpst)
{
    $tmppl .= "
              <img class='battleimg' src='Core/battlegrid.php'>
	    ";

    $tmpst .= "
		";
}

function getbattlelist(&$tabcontent, &$tabcontentstyle, &$ajaxcode)
{
    $batres = getuserbattles($batcnt);
    $tabcontent .= "<div class='myemboss'><div class='batdiv myscroller'>";
    for ($i = 0; $i < $batcnt; $i++) {
        $batarr = query_fetch_array($batres);
        $batid = $batarr['batid'];
        $batfin = $batarr['finished'] == 'Y';
        if ($i == 0 and !myisset(getsessionvar('selbattle'))) {
            $_SESSION['selbattle'] = $batid;
        }
        $scx = $batarr['bcoordx'];
        $scy = $batarr['bcoordy'];
        $pid = getplanetfromcoords($scx, $scy);
        $batname = "Battle at sector [$scx:$scy]";
        $sttm = $batarr['bsttime'];
        $tms = date("d-m-Y H:i:s", $sttm);
        $atkfleets = getbattlefleetnumber($batid, "Y");
        $deffleets = getbattlefleetnumber($batid, "N");
        if ($batfin) {
            $bfs = "(FINISHED)";
        } else {
            $bfs = "";
        }

        if ($pid == null) {
            $ss = 'the sector';
        } else {
            $pname = getplanetname($pid);
            $ss = "planet $pname";
        }

        $top = 10 + $i * 65;
        $tabcontent .= "<div class='batdivin batdivall myinset' name='batdiv_$batid' id='batdiv_$batid'  style='top:$top;'>
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

        if ($i == 0) {
            $selectedbatid = $batid;
        }
    }

    $tabcontent .= "</div></div>";
    $tabcontent .= "<div class='fullreport myemboss ' name='fullreport'></div>";
    $tabcontentstyle = "       

		";

    $isajax = $_SESSION['isajax'];
    if (!$isajax) {
        addjsfunction('initform', "");
    } else {
        $ajaxcode .= getajaxjsfunction('initform', getbattlejavascript($selectedbatid));
    }
}

function getbattlejavascript($selbatid)
{
    if (!myisset(getsessionvar('selbatid')) || is_null(getsessionvar('selbatid'))) {
        $_SESSION['selbatid'] = $selbatid;
    }
    //adddebug("SELBATID=[$selbatid]");
    $jscript = "

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
    global $bgwidth, $bgheight, $bgoffsetx, $bgoffsety, $bgquadrh, $bgquadrv, $redzonex, $redzonewidth, $quadsizex, $quadsizey;

    $inpparam=filter_input(INPUT_GET, 'param');
    if (myisset($inpparam)) {
        switch ($inpparam) {

            case 'bgoffsetx':
                return $bgoffsetx;
            case 'bgoffsety':
                return $bgoffsety;
            case 'bgwidth':
                return $bgwidth;
            case 'bgheight':
                return $bgheight;
            case 'quadsizex':
                return $quadsizex;
            case 'quadsizey':
                return $quadsizey;
            case 'bgquadrh':
                return $bgquadrh;
            case 'bgquadrv':
                return $bgquadrv;
            case 'selbatid':
                return $_SESSION['selbatid'];
        }
    }
}

function getrealbattlecoords($scx, $scy, &$rcx, &$rcy)
{
    global $bgoffsetx, $bgoffsety, $quadsizex, $quadsizey;
    $scx--;
    $scy--;
    $rcx = $bgoffsetx + $scx * $quadsizex;
    $rcy = $bgoffsety + $scy * $quadsizey;
}

function getimageforship($stid)
{
    $qr = "select image from shiptypes,x_hulls where stid=$stid and xhullid=hullid";
    if (executequery($qr, $qrres, $qrcnt) and $qrcnt > 0) {
        $dbarr = query_fetch_array($qrres);
        $img = $dbarr['image'];
        return $img;
    } else {
        return 'noship';
    }
}

function putshipongrid($shparr, &$tmpct, &$tmpst, $isatk)
{
    global $quadsizex, $quadsizey;

    $quant = $shparr['quantity'];
    $killed = $shparr['killed'];
    $scx = $shparr['scoordx'];
    $scy = $shparr['scoordy'];
    $stid = $shparr['stid'];
    $fltid = $shparr['fltid'];
    getrealbattlecoords($scx, $scy, $rcx, $rcy);
    $img = getimageforship($stid);
    $nimg = getsmallimage($img);
    $imgsml = 'Images/' . $nimg;
    $newquant = ($quant - $killed) * 1;
    $tit = "$newquant [$scx:$scy]";
    $qszx = $quadsizex - 4;
    $qszy = $quadsizey - 4;

    if ($isatk) {
        $col = 'aqua';
    } else {
        $col = '#FFC125';
    }

    $tmpct .= "
	      <div class='ships' id='ships_$fltid.$stid' data-fltid=$fltid data-stid=$stid style='position:absolute;left:$rcx;top:$rcy;z-index:2' title=$tit>
	         <img src='$imgsml'   height='$qszx' width='$qszy'>
			 <div class='shpnum' style='color:$col'><strong>$newquant</strong></div>
		  </div>

	   ";
}

function putfleetongrid($batid, $atkarr, &$tmpct, &$tmpst, $isatk, $round, &$stidarr, &$jsdataset, &$jsfleets)
{
    global $quadsizex, $quadsizey;



    $fltid = $atkarr['fltid'];

    $ownerid = $atkarr['ownerid'];

    $scx = $atkarr['scoordx'];
    $scy = $atkarr['scoordy'];

    $shpsres = getbattlefleetships($batid, $fltid, $round, $shpcnt);
    $img = $round;
    $stidarr[0] = $shpcnt;
    $quant = 0;
    $oldsize = 0;
    $speed = 9999;
    if (is_array($jsdataset)) {
        $n = count($jsdataset);
    } else {
        $n = 0;
        $jsdataset = array();
    }
    for ($j = 0; $j < $shpcnt; $j++) {
        $shparr = query_fetch_array($shpsres);
        $killed = $shparr['killed'];
        $quant += $shparr['quantity'] - $killed;
        $stid = $shparr['stid'];
        //$uid = $shparr['userid'];
//        $username= getusername($uid);
//        $shparr['username']=$username;
        // $shparr=getshipinfo($shparr);//todo:get the maxsize of shiptype
        $stidarr[$stid] = $stid;
        $size = 0; //$shparr['maxsize'];
        $speed = 0; //min($shparr['maxsize'],$speed);
        $jsdataset[$n++] = $shparr;
        if ($size >= $oldsize) { //show the biggest size ship image
            $img = getimageforship($stid);
            $oldsize = $size;
        }
    }

    getrealbattlecoords($scx, $scy, $rcx, $rcy);
    $nimg = getsmallimage($img);
    $imgsml = 'Images/' . $nimg;
    $atkarr['image'] = $imgsml;
    //	   if ($jsfleets==null)
    //	      $n=0;
    //	   else
    //	      $n=count($jsfleets);
    $jsfleets[$fltid] = $atkarr;


    $newquant = $quant;
    $tit = "'$newquant ($shpcnt shiptypes) \n [$scx:$scy][$rcx,$rcy]'";

    $qszx = $quadsizex - 4;
    $qszy = $quadsizey - 4;

    if ($isatk) {
        $col = 'aqua';
    } else {
        $col = '#FFC125';
    }

    $tmpct .= "
	      <div class='ships' id='ships_$fltid' data-fltid=$fltid  style='position:absolute;left:$rcx;top:$rcy;z-index:4' title=$tit>
	         <img src='$imgsml'   height='$qszx' width='$qszy'>
			 <div class='shpnum' style='color:$col'><strong>$newquant</strong></div>
		  </div>

	   ";
}

//arr = [{key: key1, value: value1}, {key: key2, value: value2}];

function getbattleround(&$tmpct, &$tmpst, &$jscr, $batid, $round)
{
    $jsdataset = null;
    $jsfleets = null;
    $stidarr = null;

    $tmpct .= "
 			<div class='battleroundin' name='battleroundin'>
		";

    $n = 0;
    $ftp = "Y";
    //$nn = 0;
    $username='UnKnown??'.$batid."_$round";
    $jsdataset=[];
    $jsfleets=[];
    do {
        $atkres = getbattlefleets($batid, $round, $ftp, $atkcnt, true); //get all fleets 1st for attacker then for defender
        for ($i = 0; $i < $atkcnt; $i++) {
            $atkarr = query_fetch_array($atkres);
            $fltid = $atkarr['fltid'];
            // $ownerid=$atkarr['ownerid'];
            //$shpsres=getbattlefleetships($batid,$fltid,$round,$shpcnt);
            putfleetongrid($batid, $atkarr, $tmpct, $tmpst, $n == 0, $round, $stidarr, $jsdataset, $jsfleets);
            $username = getusername($atkarr['ownerid']);
            $jsfleets[$fltid]['username'] = $username;

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
        if ($ftp == 'Y') {
            $atkusername = $username;
        } else {
            $defusername = $username;
        }
        $ftp = "N";
    } while (++$n < 2); //do
    $tmpct .= "</div>";


    $atkreport = getbattlefleetreport($batid, $round);
    $defreport = getbattlefleetreport($batid, $round, 'N');



    $tmpst .= "
		";

    //add shiptype info
    if (is_array($stidarr)) {
        foreach ($stidarr as $key => $value) {
            $shparr = getallshipinfo($key);
            $stpdataset[$key] = $shparr;
        }
    } else {
        $stpdataset=[];
    }

    $jsflt_array = json_encode($jsfleets);
    $js_array = json_encode($jsdataset);
    $stp_array = json_encode($stpdataset);
    $jscr = "var atkuser='$atkusername';var defuser='$defusername';" .
            " var fleetsarr =  $jsflt_array;\n  " .
            " var shipsarr =  $js_array;\n  " .
            " var stypearr =  $stp_array;\n  " .
            " var atkreport =  `$atkreport`;\n  " .
            " var defreport =  `$defreport`;\n  "
    ;
}

function getbattlecontrols(&$tmpct, &$tmpst, $rnd, $maxround)
{
    $tmpct .= "<div class='roundcontrols'>

			      <div class='tpstr tape' id='tpstr' data-action='start' title='to round 1'>  </div>
			      <div class='tppre tape' id='tppre' data-action='previous' title='to previous round'> </div>				  
			      <div class='tpnxt tape' id='tpnxt' data-action='next' title='to next round'> </div>				  
			      <div class='tpend tape' id='tpend' data-action='end' title='to last round'> </div>				  
			      <div class='movcmd tape' id='movcmd' data-action='move' title='move ships'> </div>
	       
               <div class='tpinfo myinset' id='tpinfo'>			    
	        Round $rnd of $maxround
 	       </div>
               <div class='atkuser alluser myinset' id='atkuser'></div>
               <div class='defuser alluser myinset' id='defuser'></div>
               <div class='atkreport report myscroller myinset' name='atkreport'> </div>
               <div class='defreport report myscroller myinset' name='defreport'> </div>


   	";


    $tmpst .= "

		";
}

function getbattlefinalreport($batid, &$cont, &$contstyle)
{
    if (getreportforbattle($batid, $batrep)) {
        $cont = "<div class='myscr myscroller '><div class='finbattle myinset'>" . $batrep . "</div></div>";
        $contstyle = "
            ";
    } else {
        $cont = "Battle not finished yet!!!";
        $contstyle = "
        ";
    }
}

//this is called by client through ajax return the tab info
function battleinforequested($info)
{
    db_connect();
    activityoccur();
    $ajaxcode = '';
    $sntback = '';
    switch ($info) {

        case 0:
        case 6:
        case -1:
            //battle map
            $cont = '';
            $contstyle = '';
            getbattlegrid($cont, $contstyle);
            $sntback = "<style type='text/css'>" . $contstyle . "</style>" . $cont;
            //$retarr['content']=$sntback;
            break;
        case 1:
            getbattlelist($tabcontent, $tabcontentstyle, $ajaxcode);
            $sntback = "<style type='text/css'>" . $tabcontentstyle . "</style>" . $tabcontent;
            //$retarr['content']=$sntback;

            break;
        case 2:
            $tabcontent = 'TODO SHOW REPORT OF SELECTED BATTLE';
            $tabcontentstyle = '';
            $sntback = "<style type='text/css'>" . $tabcontentstyle . "</style>" . $tabcontent;
            //$retarr['content']=$sntback;
            break;

        case 20: //get html for battle round=round
            $inpround=filter_input(INPUT_GET, 'round');
            if (myisset($inpround)) {
                $rnd = $inpround;
                $batid = filter_input(INPUT_GET, 'batid');
                getbattleround($cont, $contstyle, $ajaxcode, $batid, $rnd);
                $sntback = "<style type='text/css'>" . $contstyle . "</style>" . $cont;
                //   $retarr['content']=$sntback;
            }

            break;
        case 21: //get html for battle controls
            $inpround=filter_input(INPUT_GET, 'round');
            $batid = filter_input(INPUT_GET, 'batid');
            if (myisset($inpround)) {
                $rnd = $inpround;
                $maxround = getbattlenextround($batid);
            } else {
                if (myisset($batid)) {
                    $maxround = getbattlenextround($batid);
                    $rnd = $maxround;
                } else {
                    $rnd = 0;
                    $maxround = 99;
                }
            }
            $retarr['maxround'] = $maxround;
            getbattlecontrols($cont, $contstyle, $rnd, $maxround);
            $sntback = "<style type='text/css'>" . $contstyle . "</style>" . $cont;
            // $retarr['content']=$sntback;
            break;
        case 22://get html for final battle report
            $inpbatid=filter_input(INPUT_GET, 'batid');
            if (myisset($inpbatid)) {
                $batid = $inpbatid;
                getbattlefinalreport($batid, $cont, $contstyle);
            } else {
                $cont = "ERROR no batid was set.<br>";
            }
            $sntback = "<style type='text/css'>" . $contstyle . "</style>" . $cont;
            break;
        case 50: // set ship movement
            $inpfltid=filter_input(INPUT_GET, 'fltid');
            if (myisset($inpfltid)) {
                $batid = filter_input(INPUT_GET, 'batid');
                $fltid = $inpfltid;
                //$stid=filter_input(INPUT_GET, 'stid');
                $x = filter_input(INPUT_GET, 'x');
                $y = filter_input(INPUT_GET, 'y');
                $sntback = "ship $fltid will move to [$x:$y]\n ship command OK";
                $sntback .= setbattlefleettomove($batid, $fltid, $x, $y);
            }

            break;

        case 98:$retarr['param'] = ajaxgetparam();
            break;
    }
    $retarr['content'] = $sntback;
    $retarr['scriptcode'] = $ajaxcode;
    if ($info==6) {
        adddebug("INFO was 6<br>");
    }
    getdebugdata($retarr);
    
    echo json_encode($retarr);
}

function battlemain()
{
    global $bgwidth;

    $tabarr[0] = 'BATTLES';
    // $tabarr[1] = 'REPORT';

    $maintab = "battabmain";


    $tabcontent = "<div class='$maintab defmaintab' name='$maintab'> </div>";

    $tabcontentstyle = "
		 .$maintab
		 {
                   width:430px;
		 }
  		";

    $lft = $bgwidth + 5;
    $tp = 130;
    $hgt = 1024 - $tp - 100 - 150; //150 is top menu //100 is tab top
    createtab3($maintab, $tabarr, $lft, $tp, 1020 - $lft, $hgt, $tabcontent, 'tabpressed', 140);



    addoutput("", $tabcontentstyle);


    //tab pressed so get data through ajax
    $jscript = "


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
                         case 1:
			    info=2;
			 break;
			}

			   obj=getAjaxInfo(info,'battle','p=0','myscript');
			   divobj=getElementByName('$maintab');
                           if (obj!=null && obj.hasOwnProperty('content'))    
			    divobj.innerHTML=obj.content;

			 // call a default function if exists
				if (typeof initform == 'function') {
				  initform();
				}//	else alert('no func');

                            if (info==1){
                              mybatid=getAjaxParam('battle','selbatid');
                              newbattle(mybatid);
                            }
		   }



		";

    addjscript($jscript);

    $incjsf = getjsincludefile('jscript/common.js');
    addincludefile($incjsf);
    $incjsf = getjsincludefile('jscript/battle.js');
    addincludefile($incjsf);
}

function init_page()
{
    checkparams();

    $cont = "<div class='divbattlearea' name='divbattlearea' >";
    $contstyle = "
		   ";

    adddebug("INIT BATTLE PAGE<BR>");
    //get main design for battle
    getbattledesign($cont, $contstyle);
    $cont .= "<div name='divmesg'>PLEASE SELECT A BATTLE</div>"
            . "</div>";
    addoutput($cont, $contstyle);

    battlemain();
}

//Default page commands
$inpinfo=filter_input(INPUT_GET, 'info', FILTER_VALIDATE_INT);
$inptest=filter_input(INPUT_GET, 'test', FILTER_VALIDATE_INT);
adddebug("TEST=$inptest<br>");
if (myisset($inpinfo) or myisset($inptest)) {
    //$_SESSION['isajax']=true;
    adddebug("INFO=$inpinfo<br>");
    battleinforequested($inpinfo);
    //$_SESSION['isajax']=false;
} else {
    $_SESSION['isajax'] = false;
    adddebug("INFO***=$inpinfo<br>");
    $selplanet = $_SESSION['selplanet'];
    if (myisset($selplanet)) {
        doendturn($selplanet);
    } else {
        doendturn();
    }
}