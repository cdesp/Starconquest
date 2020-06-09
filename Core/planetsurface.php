<?php

include_once "common.php";
include_once("galaxyutils.php");

//map planet coords
function getplanetmapcoords($x, $y, &$scx, &$scy)
{
    global $solsyssize;

    $solx = $_SESSION['solx'];
    $soly = $_SESSION['soly'];


    $scx = $solx * $solsyssize + $x;
    $scy = $soly * $solsyssize + $y;
}

function getbuildinghtml($qrarr, &$t, &$tstl)
{
    $buildsizex = 130;
    $buildsizey = 130;

    $bx = $qrarr['dsgn_x'];
    $by = $qrarr['dsgn_y'];
    $bname = $qrarr['bname'];
    $blevel = $qrarr['blevel'];
    $img = $qrarr['btimage'];
    if ($img == null) {
        $img = 'residential.png';
    } else {
        $img .= '.png';
    } //add .png to image

    $btid = $qrarr['btid'];
    $tid = $qrarr['pid'] . '_' . $btid;

    //$hint="$bname ($blevel)";
    $hint = "";
    $zi = 10;
    $t .= ' <div id="build_' . $tid . '" class="buildingclass" title="' . $hint . '" data-id=' . $btid . ' data-img=' . $qrarr['btimage'] . ' > </div> ';

    $tstl .= "
	   #build_$tid  { display: block;
    background-image: url('Images/buildings/$img') ;
	 position: absolute;
	 z-index: $zi;
	left: " . $bx . "px;
	top: " . $by . "px;
    height: " . $buildsizex . "px; 
    width: " . $buildsizey . "px; 
		} 
		";

    $bx = $bx + 5;
    $by = $by + $buildsizey - 10;
    $t .= ' <div id="buildtext_' . $tid . '" class="buildingtextclass" title="' . '' . '"> ' . $bname . " ($blevel)" . ' </div> ';
    $tstl .= "
	   #buildtext_$tid  { 	   
			left: " . $bx . "px;
			top: " . $by . "px;	
		} 
		";
}

function getplanetsurface($pid)
{
    $plansurf = '';
    $plansurfstyle = '';

    $plansurf .= ' <div id="planet" class="planetclass" > </div> ';
    $plansurfstyle .= "
		";

    $qres = getbuildingsforplanet($pid, $qrcnt);
    //	adddebugval("planet cnt",$qrcnt);
    for ($i = 0; $i < $qrcnt; $i++) {
        $qrarr = query_fetch_array($qres);
        getbuildinghtml($qrarr, $plansurf, $plansurfstyle);
    }

    return $plansurf . "<style type='text/css'>" . $plansurfstyle . "</style>";
}

function fillplanetdata($pid)
{
    $ajaxcd = "var selplanet=$pid;";

    checkifbuildingsupgraded($pid);
    $qres = getbuildingsforplanet($pid, $qrcnt);

    for ($i = 0; $i < $qrcnt; $i++) {
        $trows = query_fetch_array($qres);
        $bldid = $trows['btid'];

        //$goldupg=$trows['goldupg'];
        //$metalupg=$trows['metalumupg'];
        //$tritiumupg=$trows['tritiumupg'];
        //$blvl=$trows['blevel'];
        //getupgresneeded($goldupg,$metalupg,$tritiumupg,$blvl+1);
        //$trows['goldupg']=$goldupg;
        //$trows['metalumupg']=$metalupg;
        //$trows['tritiumupg']=$tritiumupg;


        $buildarr[$bldid] = $trows;
    }
    $building_array = json_encode($buildarr);

    $ajaxcd .= "" .
            " var bcount = " . $qrcnt . ";\n  " .
            " var buildingsArr = " . $building_array . ";\n  ";


    return $ajaxcd;
}

function showplanetsurface($isajax = false)
{
    global $planetstr, $planetstyl;
    $ajaxcode = '';




    if (!$isajax) {
        addjscript($ajaxcode);
        addonloadfunction('getPlanetData();');
        $incjsf = getjsincludefile('jscript/planetdesign.js');
        addincludefile($incjsf);
    } else {
        $ajaxcode .= getajaxjsfunction('initform2', $ajaxcode);
    }
}
