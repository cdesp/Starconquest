<?php
    session_start();

    require_once "common.php";
    require_once "myutils.php";
    require_once "gamesession.php";
    require_once "galaxyutils.php";
    
    global $mapoffsetx,$mapoffsety,$tilesizex,$tilesizey,$maxplanets,$ssx,$ssy;
    
    $dbg='';
    
    function getrealcoords(&$x, &$y)
    {
        global $mapoffsetx,$mapoffsety,$tilesizex,$tilesizey,$solsyssize,$soloffx,$soloffy;
        $x=$mapoffsetx+($x-($soloffx*$solsyssize))*$tilesizex;
        $y=$mapoffsety+($y-($soloffy*$solsyssize))*$tilesizey-128;
            
        //x,y is upper left corner
            
        $x+= $tilesizex/2;
        $y+= $tilesizey/2;
            
        $x=max($x, $mapoffsetx);
        $y=max($y, $mapoffsety-128);
    }
    
    function paintroute($img, $rtid=null)
    {
        //		global $mapoffsetx,$mapoffsety,$tilesizex,$tilesizey,$ssx,$ssy,$solsyssize,$soloffx,$soloffy;
        global $tilesizex,$tilesizey;
        global $dbg;
        global $red,$green,$blue;
        if (myisset(getsessionvar('routesel'))) {
            $rtsel=$_SESSION['routesel'];
        }
        if (myisset(getsessionvar('selfleet'))) {
            $selfleet=$_SESSION['selfleet'];
            $rtsel=getfleetonroute($selfleet);
        } else {
            $rtsel=null;
        }
        

        
        if ($rtid==null) {
            $dbg.="routesel=".$_SESSION['routesel'].'<br>';
            $rtid=$rtsel;
        }
        
        //$dbg.="$rtsel=$rtid<br>";
        if ($rtid==$rtsel) {
            $col=$red;
            //  $dbg.="RED<br>";
        } else {
            $col=$blue;
            // $dbg.="BLUE<br>";
        }
        
    
        
        $x=0;
        $y=0;
        if ($rtid!=null) {
            // $dbg.="routeid=$rtid<br>";
            $wayarr=getwaypointsforroute($rtid);
            $arrcnt=count($wayarr);
            if ($arrcnt>0) {
                $xpre=$wayarr[0]['wx'];
                $ypre=$wayarr[0]['wy'];
                // $dbg.="0. xst,yst=".$xpre.':'.$ypre.'<br>';
                getrealcoords($xpre, $ypre);
           
         
                for ($i=1;$i<$arrcnt;$i++) {
                    $x=$wayarr[$i]['wx'];
                    $y=$wayarr[$i]['wy'];
                    $dbg.="$i. x,y=".$x.':'.$y.'<br>';
                    getrealcoords($x, $y);
            
                    //	$dbg.="$i. rx,ry=".$x.':'.$y.'<br>';
                    imageline($img, $xpre, $ypre, $x, $y, $col);
                    $xpre=$x;
                    $ypre=$y;
                }
                imagearc($img, $x, $y, $tilesizex/2, $tilesizey/2, 0, 360, $col);
            }
        }
    }



    

    $solsyswidth=$solsyssize*$tilesizex;
    $mapwidth=$solsyswidth*3;
        
//		$dbg.= "mofsx=$mapoffsetx<br>";
//	    $dbg.= "mofsy=$mapoffsety<br>";
        
        $imgwidth=$mapoffsetx+$mapwidth+20+1;
        $imgheight=$mapoffsety+$mapwidth+1-128;
//		$dbg.= "imgw=$imgwidth<br>";
//		$dbg.= "imgh=$imgheight<br>";
        
        $img = imageCreateTransparent($imgwidth, $imgheight);
        $green = imagecolorallocate($img, 132, 135, 28);
         $red = imagecolorallocate($img, 220, 20, 60);
         $blue = imagecolorallocate($img, 67, 110, 238);
    
        if (selectfleetmode()) {
            ImageGrid($img, $mapoffsetx, $mapoffsety-128, $tilesizex, $tilesizex, 3*$solsyssize, 3*$solsyssize, $green);
            ImageGrid($img, $mapoffsetx+1, $mapoffsety+1-128, $solsyswidth, $solsyswidth, 3, 3, $green);
            ImageGrid($img, $mapoffsetx-1, $mapoffsety-1-128, $solsyswidth, $solsyswidth, 3, 3, $green);
        } else {
            ImageGrid($img, $mapoffsetx, $mapoffsety-128, $solsyswidth, $solsyswidth, 3, 3, $green);
        }
    

        
        if ($ssx-1<0) {
            $adn=1;
        } else {
            $adn=0;
        }

        for ($x=0;$x<3;$x++) {
            $tx=$mapoffsetx+($solsyswidth/2)+($solsyswidth*$x)-10;
            $ty=$mapoffsety-128-15;
            $tit=$ssx-1+$x+$adn;
            imagestring($img, 3, $tx, $ty, $tit, $red);
        }
        if ($ssy-1<0) {
            $adn=1;
        } else {
            $adn=0;
        }
        for ($y=0;$y<3;$y++) {
            $tx=$mapoffsetx-14;
            $ty=$mapoffsety-128+($solsyswidth/2)+($solsyswidth*$y)-10;
            $tit=$ssy-1+$y+$adn;
            imagestring($img, 3, $tx, $ty, $tit, $red);
        }
        

         if (myisset(getsessionvar('showroutes'))) {
             $qres=getalluserroutes();
             $qcnt=query_num_rows($qres);
             for ($i=0;$i<$qcnt;$i++) {
                 $trows = query_fetch_array($qres);
                 $rtid=$trows['rtid'];
                 $dbg.='rtid='.$rtid.'<br>';
                 paintroute($img, $rtid);
             }
         } else {
             paintroute($img);
         }

       
        $_SESSION['dbg']=$dbg;
        // Output and free from memory
        header('Content-Type: image/png');

        imagepng($img);
        imagedestroy($img);
