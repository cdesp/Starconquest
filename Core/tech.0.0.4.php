<?php

include_once "mytabset.php";
include_once("galaxyutils.php");
include_once("myutils.php");
include_once("gamesession.php");

includecorepage("techutils");
includecorepage("techdesign");

include_once("srvendturn.php");

//this is called by client through ajax return the tab info
function techinforequested($info)
{
    db_connect();
    activityoccur();
    // $selplanet = $_SESSION['selplanet'];
    //$playerid = $_SESSION['id'];
    $ajaxcode = '';
    $retarr['content'] = '';
    adddebug('check tech discovered');
    checktechdiscovered();

    switch ($info) {
        case 0://main map area
            //$retarr['content']=getplanetsurface($selplanet);

            break;

        case 1:
            $ajaxcode = gettechdataforcat(1);
            break;
        case 2:
            $ajaxcode = gettechdataforcat(2);
            break;
        case 3:
            $ajaxcode = gettechdataforcat(3);
            break;
        case 4:

            break;
        case 30:
            //   $ajaxcode=filltechtreedata();
            break;
    }
    $retarr['scriptcode'] = $ajaxcode;
    getdebugdata($retarr);

    echo json_encode($retarr);
}

function techmain()
{
    global  $qrcnt, $tabarr;

    $qrres = gettechcat($qrcnt);

    adddebugval('catno', $qrcnt);
    for ($i = 0; $i < $qrcnt; $i++) {
        $dbarr = query_fetch_array($qrres);
        $tabarr[$i] = $dbarr['catname'];
        adddebugval('cat', $dbarr['catname']);
    }


    $maintab = "techtabmain";


    $tabcontent = "<div id='$maintab' class='$maintab defmaintab' name='$maintab'> </div>";
    $tabcontentstyle = "
		";
    $tp=130;
    $hgt=1024-$tp-100-150;//150 is top menu //100 is tab top
    createtab3($maintab, $tabarr, 5, $tp, 1000, $hgt, $tabcontent, 'tabpressed', 140);

    addoutput("", $tabcontentstyle);

    //tab pressed so get data through ajax
    $jscript = "
		    var mainpage='tech';
		
		   function tabpressed(cobj,id)
		   {
			   
			  
			   info=id+1;
			console.log(info);
			   obj=getAjaxInfo(info,'tech','p=0','myscript');
			   if (obj.content!=''){
			     divobj=getElementByName('$maintab'); 
			     divobj.innerHTML=obj.content;	
			   }

				 
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

function checkbuttons()
{
    if (myisset(filter_input(INPUT_POST, 'submit'))) {
        if (filter_input(INPUT_POST, 'submit') == 'upg') {
            $tid = filter_input(INPUT_POST, 'techid');
            checktechdiscovered();
            adddebug("Upgrade TechID:$tid<BR>");
            //todo:Call Upgrade Tech and get the result message "can't upgrade" when not enough techpoints
            $mes = upgradetech($tid);
            if ($mes <> '') {
                adddebug("$mes<BR>");
            }
        }
    }

    //	  if (myisset(filter_input(INPUT_GET, 'action'))){
//	     $act=filter_input(INPUT_GET, 'action');
//	  	 switch($act) {
//		   case "upgrade":
//		                  $compid=filter_input(INPUT_GET, 'bid');
//		   								upgradebuilding($selplanet,$compid);
//	 	 									break;
//		 }
//	  }
}

//execute to create the page
function init_page()
{
    checktechdiscovered();
    getsessionvars();
    checkbuttons();


    //map
    $cont = "<div class='divtecharea' id='divtecharea'>";
    $contstyle = "
	   ";
    techmain();
    addoutput($cont, $contstyle);
    
    addoutput('</div>', '');
    showtechtree(false);
}

//default page commands

if (myisset(filter_input(INPUT_GET, 'info'))) {
    $_SESSION['isajax'] = true;
    adddebug('Tech Page in Ajax<BR>');
    techinforequested(filter_input(INPUT_GET, 'info'));
    adddebugval('INFO', filter_input(INPUT_GET, 'info'));
    $_SESSION['isajax'] = false;
} else {
    $_SESSION['isajax'] = false;

    $selplanet = $_SESSION['selplanet'];
    if (myisset($selplanet)) {
        doendturn($selplanet);
    } else {
        doendturn();
    }
}
?>

