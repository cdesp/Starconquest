<?php

    include_once "myutils.php";
    
    function solsysoffset()
    {
        global $ssx,$ssy,$galaxysize,$soloffx,$soloffy;
        $soloffx=max($ssx-1, 0);
        $soloffy=max($ssy-1, 0);
    }
    
    

    function getsessionvars()
    {
        global $maxplanets,$ssx,$ssy,$galaxysize,$solsyssize,$solsyscover;
        global $solx,$soly;
        if (myisset(getsessionvar('maxplanets'))) {
            $maxplanets=   getsessionvar('maxplanets');
        } else {
            $maxplanets=10;
        }
    
        if (!myisset(getsessionvar('ssx'))) {
            $ssx=10;
            $ssy=14; //quadrant to center
        } else {
            $ssx = $_SESSION['ssx'];
            $ssy = $_SESSION['ssy'];
        }
   
        solsysoffset();
   
        if (myisset(getsessionvar('solx'))) {//selected plane solar system
            $solx = $_SESSION['solx'];
            $soly = $_SESSION['soly'];
        }


        if (myisset(getsessionvar('galaxysize'))) {
            $galaxysize=$_SESSION['galaxysize'];
            $solsyssize=$_SESSION['solsyssize'];
            $solsyscover=$_SESSION['solsyscover'];
            $maxplanets=$_SESSION['maxplanets'];
        }
    }
    
    getsessionvars();
        //adddebugval("selp","$selplanet");
