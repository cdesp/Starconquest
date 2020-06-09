<?php

    include "common.php";
    
    
    
    function ajaxgetsessionparam()
    {
        global $retarr;
        
        $inpsparam=filter_input(INPUT_GET, 'sparam');
        if (myisset($inpsparam)) {
            $reqparam=$inpsparam;
            if (myisset(getsessionvar($reqparam))) {
                $retarr['sparam']=$_SESSION[$reqparam];
            } else {
                $retarr['sparam']= "wrong session param($reqparam) not exists";
            }
        }
    }
    
    
try {
    session_start();
    activityoccur();
    $_SESSION['isajax']=true;

    
    $inppg=filter_input(INPUT_GET, 'pg');
    adddebug("INPUT PAGE=$inppg<br>");
    if (myisset($inppg)) {
        $pg=$inppg;
        if (!includeajaxpage($pg)) {
            $retarr['content']= "error finding page [$pg]";
        }
    }
    
    ajaxgetsessionparam();
} catch (Exception $e) {
    $retarr['debugdata'] .= 'Caught exception: '+  $e->getMessage()+ "\n";
}
    
    //this is only for this page return error messages and sessionparams
    if (myisset($retarr)) {
        echo json_encode($retarr);
    }
 
    $_SESSION['isajax']=false;
