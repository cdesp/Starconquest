<?php
    session_start();

    require_once "myutils.php";
    require_once "gamesession.php";
    global $mapoffsetx,$mapoffsety,$tilesizex,$tilesizey,$maxplanets,$solx,$soly,$solsyssize;

    //show one solar system tiles are 3 times bigger
    $planetwidth=$tilesizex*3;
    $solsyswidth=$solsyssize*$planetwidth;
    $mapwidth=$solsyswidth;
    
    
        $img = imageCreateTransparent($mapoffsetx+$mapwidth+20+1, $mapoffsety+$mapwidth+1);
        $green = imagecolorallocate($img, 132, 135, 28);
    
        ImageGrid($img, $mapoffsetx, $mapoffsety-128, $planetwidth, $planetwidth, 10, 10, $green);
    
         $red = imagecolorallocate($img, 220, 20, 60);
        for ($x=0;$x<$solsyssize;$x++) {
            $tx=$mapoffsetx+($planetwidth/2)+($planetwidth*$x)-10;
            $ty=$mapoffsety-128-15;
            imagestring($img, 3, $tx, $ty, ($solx+0)*$solsyssize+$x, $red);
        }
        for ($y=0;$y<$solsyssize;$y++) {
            $tx=$mapoffsetx-20;
            $ty=$mapoffsety-128+($planetwidth/2)+($planetwidth*$y)-10;
            imagestring($img, 3, $tx, $ty, ($soly+0)*$solsyssize+$y, $red);
        }

        // Output and free from memory
        header('Content-Type: image/png');

        imagepng($img);
        imagedestroy($img);
