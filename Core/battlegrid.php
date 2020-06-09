<?php

session_start();

require_once "common.php";
require_once "myutils.php";
require_once "gamesession.php";
require_once "galaxyutils.php";



$dbg = '';

function getthegrid(&$img, &$dbg)
{
    global $bgwidth, $bgheight, $bgquadrh, $bgquadrv, $bgoffsetx, $bgoffsety, $redzonex;
    global $quadsizex, $quadsizey, $redzonewidth;



    $imgwidth = $bgoffsetx + $bgwidth + 1;
    $imgheight = $bgoffsety + $bgheight + 1;
    //		$dbg.= "imgw=$imgwidth<br>";
    //		$dbg.= "imgh=$imgheight<br>";

    $img = imageCreateTransparent($imgwidth, $imgheight);
    $green = imagecolorallocate($img, 132, 135, 28);
    $red = imagecolorallocate($img, 220, 20, 60);
    $blue = imagecolorallocate($img, 67, 110, 238);

    ImageGrid($img, $bgoffsetx, $bgoffsety, $quadsizex, $quadsizey, $bgquadrh, $bgquadrv, $green);

    $redzx = $redzonex * $quadsizex;
    imagerectangle($img, $bgoffsetx + $redzx - $redzonewidth * $quadsizex, $bgoffsety, $bgoffsetx + $redzx + $redzonewidth * $quadsizex, $bgheight, $red);
    imagerectangle($img, $bgoffsetx + $redzx - $redzonewidth * $quadsizex - 1, $bgoffsety, $bgoffsetx + $redzx + $redzonewidth * $quadsizex - 1, $bgheight, $red);
    imagerectangle($img, $bgoffsetx + $redzx - $redzonewidth * $quadsizex + 1, $bgoffsety, $bgoffsetx + $redzx + $redzonewidth * $quadsizex + 1, $bgheight, $red);


    for ($x = 0; $x < $bgquadrh; $x++) {
        $tx = $bgoffsetx + $x * $quadsizex + ($quadsizex / 2) - 2;
        $ty = $bgoffsety - 15;
        $tit = ($x + 1) % 10;
        imagestring($img, 3, $tx, $ty, $tit, $red);
        $tit = floor(($x + 1) / 10);
        imagestring($img, 3, $tx, $ty - 10, $tit, $red);
    }

    for ($y = 0; $y < $bgquadrv; $y++) {
        $tx = $bgoffsetx - 17;
        $ty = $bgoffsety + $y * $quadsizey + ($quadsizey / 2) - 6;
        if ($y < 9) {
            $tit = '0' . ($y + 1);
        } else {
            $tit = ($y + 1);
        }
        imagestring($img, 3, $tx, $ty, $tit, $red);
    }
}

getthegrid($img, $dbg);

$_SESSION['dbg'] = $dbg;
// Output and free from memory
header('Content-Type: image/png');

imagepng($img);
imagedestroy($img);
