<?php

include_once "mytabset.php";
include_once("galaxyutils.php");
include_once("myutils.php");
include_once("gamesession.php");
include_once("srvendturn.php");
//map
includecorepage("mapdesign");
//tabs
includecorepage("mapplanetlist");
includecorepage("mapfleetlist");
includecorepage("maproutelist");

function doaction($act)
{
    switch ($act) {

        case 'planetcenter':
            centertoplanet(getsessionvar('selplanet'));
            break;
        case 'fleetcenter':
            centertofleet(getsessionvar('selfleet'));
            break;
    }
    unset($_SESSION['action']);
}

//this is called by client through ajax return the tab info
function mapinforequested($info)
{
    //adddebug('getmap0<BR>');
    db_connect();
    activityoccur();

    $ajaxcode = '';

    switch ($info) {
        case 0://main map area
            //adddebug('getmap<br>');
            $retarr['content'] = getmap(true);
            break;

        case 1:
            getplanetlist($tabcontent, $tabcontentstyle, $ajaxcode);
            $sntback = "<style type='text/css'>" . $tabcontentstyle . "</style>" . $tabcontent;
            $retarr['content'] = $sntback;

            break;
        case 2:
            checkroutesmove();
            getfleetlist($tabcontent, $tabcontentstyle, $ajaxcode, $_SESSION['id']);
            $sntback = "<style type='text/css'>" . $tabcontentstyle . "</style>" . $tabcontent;
            $retarr['content'] = $sntback;
            break;
        case 3:
            checkroutesmove();
            getroutelist($tabcontent, $tabcontentstyle, $ajaxcode, $_SESSION['id']);
            $sntback = "<style type='text/css'>" . $tabcontentstyle . "</style>" . $tabcontent;
            $retarr['content'] = $sntback;
            break;

        case 30:
            adddebug('getmapdata');
            getmapdata($ajaxcode);
            break;

        case 50: // set ship movement
            $inpfltid=filter_input(INPUT_GET, 'fltid');
            if (myisset($inpfltid)) {
                $fltid = $inpfltid;
                $x = filter_input(INPUT_GET, 'x');
                $y = filter_input(INPUT_GET, 'y');

                if (!isfleetours($fltid)) {
                    $retarr['content'] = 'This is not your fleet!!!';
                    break;
                }
                //check if in battle
                if (!isfleetinbattle($fltid)) {
                    adddebug("ship $fltid will move to [$x:$y]\n ship command OK<br>");
                    $retarr['content'] = addroute($fltid, $x, $y);
                } else {
                    $retarr['content'] = 'Fleet in battle!!!';
                }
            }
            break;
    }
    $retarr['scriptcode'] = $ajaxcode;

    getdebugdata($retarr);

    echo json_encode($retarr);
}

function mapmain()
{
    adddebug('mapmain<BR>');

    $tabarr[0] = 'PLANETS';
    $tabarr[1] = 'FLEETS';
    $tabarr[2] = 'ROUTES';

    $maintab = "maptabmain";


    $tabcontent = "<div class='$maintab defmaintab' name='$maintab'> </div>";
    $tabcontentstyle = "
		 .$maintab
		 {
                    width:513px;
		 }
		";

    $tp=130;
    $hgt=1024-$tp-100-150;//150 is top menu //100 is tab top
    createtab3($maintab, $tabarr, 505, $tp, 515, $hgt, $tabcontent, 'tabpressed', 124);

    addoutput("", $tabcontentstyle);

    //tab pressed so get data through ajax
    $jscript = "
		    var mainpage='map';
		
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
			 case 2:
			    info=3;
			 break;
			 
			}
			   console.log('Info='+info);
			   obj=getAjaxInfo(info,'map','p=0','myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
				

				 
			 // call a default function if exists
				if (typeof initform == 'function')  
				  initform(); 
				if (typeof initform2 == 'function') 
				  initform2(); 
			 
		   }

		
		";

    addjscript($jscript);

    $incjsf = getjsincludefile('jscript/common.js');
    addincludefile($incjsf);
}

function checkparams()
{
    $inpplaninfo=filter_input(INPUT_GET, 'planinfo');
    if (myisset($inpplaninfo)) {
        $_SESSION['planinfo'] = $inpplaninfo;
    }
}

function init_page()
{
    adddebug('Init Map');
    checkparams();

    //map
    $cont = "<div class='divleftarea dlamap' id='divleftarea'>";
    $contstyle = "";
    addoutput($cont, $contstyle);
    getmap();
    $cont = '</div>';
    addoutput($cont, '');
    adddebug("mapmain NO AJAX<BR>");
    mapmain();
    //adddebug("Test Battle Ssystem fro flt 5<BR> ");
    //fleetdestreached(7); //for testing battles
    //battlefinished(1,true);
}

//page commands

if (myisset(getsessionvar('action'))) {
    doaction($_SESSION['action']);
}
$inpinfo=filter_input(INPUT_GET, 'info');
if (myisset($inpinfo)) {
    adddebug("mapinf<BR>");
    //$_SESSION['isajax']=true;
    mapinforequested($inpinfo);
    // $_SESSION['isajax']=false;
}
