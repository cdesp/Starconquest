<?php

include_once "mytabset.php";
include_once "messages.php";
includecorepage("mapdesign");
include_once("srvendturn.php");

function showusermessages()
{
    $contout = "
			<table class='tbmesinfo' width='400' border='0' cellspacing='0' cellpadding='2'>
			  <tr>
			    <th scope='col'>Time</th>
				<th scope='col'>Category</th>
 			    <th scope='col'>From</th>
			    <th scope='col'>To</th>
			  </tr>
			
		";

    $qres = getusermessages($qrcnt);
    if ($qrcnt > 0) {
        for ($i = 0; $i < $qrcnt; $i++) {
            $qarr = query_fetch_array($qres);
            //			if ($qarr==FALSE)  {
            //			  $mysqli->data_seek($qres,0);
            //		      $qarr = query_fetch_array($qres);
            //			}

            $isread = $qarr['msgread'] != 'N';
            $tm = date('d m Y H:i:s', $qarr['msgtime']);
            if (!myisset($qarr['fromname'])) {
                $from = "sys";
            } else {
                $from = $qarr['fromname'];
            }


            $to = $qarr['toname'];
            $msg = $qarr['msg'];
            $category = $qarr['category'];

            if ($i % 2 == 0) {
                $colr = 'gray';
            } else {
                $colr = '#9C661F';
            }

            if (!$isread) {
                $extrain = "<strong>[new] ";
                $extraout = "</strong>";
            } else {
                $extrain = "";
                $extraout = "";
            }


            $contout .= "
			 <tbody bgcolor='$colr' cellspacing='0' cellpadding='0'>
			  <tr class='tr1' >
			    <td class='tcol1'>$tm</td>
			    <td class='tcol1a'>$category</td>
			    <td class='tcol2'>$from</td>
			    <td class='tcol3'>$to</td>
			  </tr>
			  <tr class='tr2' >
			    <td class='tcol4'  colspan='4' >$extrain $msg $extraout</td>
			  </tr>	
			  </tbody>
			  <tbody>
			  <tr height=5 bgcolor='white'><td colspan='4'>  </td></tr>	
			  <tbody>
			  
			  ";
        }
    }
    $contout .= "</table>";

    $contstyl = "
		
		";

    return "<style type='text/css'>" . $contstyl . "</style>" . $contout;
}

//this is called by client through ajax return the tab info
function inforequested($info)
{
    db_connect();
    $ajaxcode = '';

    switch ($info) {
        case 1:
            $retarr['content'] = showusermessages();

            break;
        case 2:
            $retarr['content'] = "ajax fetched content here for alies";

            break;
        case 3:
            $retarr['content'] = "ajax fetched content here for enemies";

            break;
        case 30: getmapdata($ajaxcode);

            break;
    }
    $retarr['scriptcode'] = $ajaxcode;

    getdebugdata($retarr);

    echo json_encode($retarr);
}

function diplomacymain()
{


    //map
    $cont = "<div class='divleftarea dladiplomacy' id='divleftarea'>  ";
    addoutput($cont, "");
    getmap();
    $cont = "</div>  ";

    $contstyle = "";

    addoutput($cont, $contstyle);



    //tabs
    //test tabs
    $tabarr[0] = 'MESSAGES';
    $tabarr[1] = 'ALIES';
    $tabarr[2] = 'ENEMIES';


    $tabcontent = '<div class="diplomain defmaintab" name="diplomain"> </div>';
    $tabcontentstyle = "
		";

    $tp=130;
    $hgt=1024-$tp-100-150;//150 is top menu //100 is tab top
    createtab3('diplotab', $tabarr, 515, $tp, 505, $hgt, $tabcontent, 'tabpressed', 150);

    addoutput("", $tabcontentstyle);

    //tab pressed so get data through ajax
    $jscript = "
		    var mainpage='diplomacy';
			
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
			
			   obj=getAjaxInfo(info,'diplomacy','p=0','myscript');
			   divobj=getElementByName('diplomain'); 
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

function init_page()
{
    diplomacymain();
}

//start page actions
$inpinfo=filter_input(INPUT_GET, 'info');
if (myisset($inpinfo)) {
    // $_SESSION['isajax'] = true;
    adddebug('Tech Page in Ajax<BR>');
    inforequested($inpinfo);
    adddebugval('INFO', $inpinfo);
    //$_SESSION['isajax'] = false;
} else {
    $_SESSION['isajax'] = false;
}
$selplanet = getsessionvar('selplanet');
if (myisset($selplanet)) {
    doendturn($selplanet);
    //checkbuttons();
} else {
    doendturn();
}
