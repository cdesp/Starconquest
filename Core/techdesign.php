<?php

function filltechtreedata($cid)
{
    adddebug("Fill Tech for cid=$cid<br>");
    $qrres = gettech($qrcnt, $cid);

    for ($i = 0; $i < $qrcnt; $i++) {
        $dbarr = query_fetch_array($qrres);
        $tid = $dbarr['techid'];
        $techarr[$tid] = $dbarr;
    }
    if ($qrcnt > 0) {
        $tech_array = json_encode($techarr);
    } else {
        $tech_array = 'null';
    }

    $ajaxcd = "" .
            " var techcatid = " . $cid . ";\n  " .
            " var techcnt = " . $qrcnt . ";\n  " .
            " var techArr = " . $tech_array . ";\n  ";


    return $ajaxcd;
}

function showtechtree2($cid, $preid = 0)
{
    $s = '';
    $tab = '';
    for ($i = 0; $i < $preid; $i++) {
        $tab .= '-------';
    }
    $qrres = gettech($qrcnt, $cid, $preid, null);
    for ($i = 0; $i < $qrcnt; $i++) {
        $dbarr = query_fetch_array($qrres);
        $s .= $tab . $dbarr['techname'] . '<br>';
        $s .= showtechtree2($cid, $dbarr['techid']);
    }

    if ($preid == 0) {
        return " show tree for catid=$cid<br>$s";
    } else {
        return $s;
    }
}

function showtechtree($isajax = false)
{
    global $planetstr, $planetstyl;
    $ajaxcode = '';
    if (!$isajax) {
        addjscript($ajaxcode);
        addonloadfunction('getTechData();');
        $incjsf = getjsincludefile('jscript/techdesign.js');
        addincludefile($incjsf);
    } else {
        $ajaxcode .= getajaxjsfunction('initform2', $ajaxcode);
    }
}

function gettechdataforcat($catno)
{
    adddebug("gettechdata for catno=$catno <BR>");
    $qrres = gettechcat($qrcnt);
    for ($i = 0; $i < $qrcnt; $i++) {
        $dbarr = query_fetch_array($qrres);
        if ($i == $catno - 1) {
            break;
        }
    }

    $catid = $dbarr['tcatdid'];

    return filltechtreedata($catid);
}
