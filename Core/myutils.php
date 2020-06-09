<?php

global $mapoffsetx, $mapoffsety, $tilesizex, $tilesizey, $debugdata;
global $body, $bodystyle, $jscript, $includedfiles, $thispg, $thispgstyle, $head, $pg;
global $bottomoutput, $topoutput, $onload;



$mapoffsetx = 22; //22
$mapoffsety = 150; //150
$tilesizex = 16;
$tilesizey = 16;

function selectfleetmode() {
    return myisset($_SESSION['route']) and $_SESSION['route'] != null;
}

function getsmallimage($imgname) {
    $pos = strpos($imgname, '.');
    if ($pos === false) {
        return $imgname;
    } else {
        return substr($imgname, 0, $pos) . '_sml' . substr($imgname, $pos, 5);
    }
}

function gettotalhours($upgdays, $upghours, $upglevel) {
    $c7 = $upglevel * ($upgdays / 100);
    $d7 = floor($c7);
    $e7 = round($c7 * $upghours, 0);
    //	   adddebug("level $upglevel<br>");
    //   	   		 adddebugval("d7",$d7);
    //  	   		 adddebugval("c7",$c7);
    //	 adddebugval("e7pre",$c7*$upghours);
    //	   		 adddebugval("e7",$e7);
    $th = $d7 * 24 + $e7;
    if ($upglevel > 1) {
        //	 adddebugval("th ",$t+$th);
        //		  adddebug("----<br>");
        $t = gettotalhours($upgdays, $upghours, $upglevel - 1);
        //		 adddebugval("t ",$t);

        return $th + $t;
    } else {
        //		 adddebugval("th ",$upghours);
        return $upghours;
    }
}

//calculates the time needed for a building to upgrade in the level in secs
function maketimetoupg($upgdays, $upghours, $upgmins) {

    // $dst=date('H',0);
    // $th=gettotalhours($upgdays,$upghours,$upglevel);
    //adddebugval("th ",$th);
    //$g7=$th/24;
    //$findays=floor($g7);
    //$finhours=($g7-$findays)*24;

    adddebug("Upgrade-->$upgdays,$upghours,$upgmins<br>");
    //	 adddebug("-----------<br>");
    //$retdt=$findays*24*60*60+($finhours-$dst)*60*60;
    //$retdt=$findays*24*60*60+$finhours*60*60;
    $retdt = (($upgdays * 24 + $upghours) * 60 + $upgmins) * 60;

    // $upgdays+=floor(($upglevel*$upgdays)/2);
    // $upghours+=$upglevel*$upghours;
    // adddebug("$upgdays $upghours<br>");
    // $retdt=$upgdays*24*60*60+($upghours-$dst)*60*60;
    //	   adddebug(gettimetoupgrade($retdt).'<br>');
    //	   adddebug('------------------------<br>');
    return $retdt;
}

//returns a string with the time
function getshiptime($tm, &$m = 0, &$d = 0) {
    $tm = deldst($tm);
    $s = '';
    $tm0 = date("m", $tm) - 1;
    $tm1 = date("d", $tm) - 1;
    if ($tm0 > 0) {
        $s .= $tm0 . 'm ';
    }
    if ($tm1 > 0) {
        $s .= $tm1 . 'd ';
    }
    $m = $tm0;
    $d = $tm1;
    return $s . date('H:i:s', $tm);
}

//calculates the time needed for a ship to upgrade in the level
function makeshiptimetoupg($upgdays, $upghours, $upgmins, $upgsecs) {
    $dst = date('H', 0); //daylight savings +2 or +3 we must adjust
    $dst = 0; //no daylight save
    $tmins = $upgdays * 24 * 60 * 60 + ($upghours - $dst) * 60 * 60 + $upgmins * 60 + $upgsecs; //everything in seconds


    return $tmins;
}

function isshiptimetoupg($sttm, $tmdur) {
    $tmdur = adddst($tmdur);
    $t = ($sttm + $tmdur) <= mtimetn();
    //	 adddebugval('tmtoupg',calctimetoupgrade($sttm,$tmdur));
    //	 adddebugval('$sttm+$tmdur',$sttm+$tmdur);
    //	 adddebugval('mtimetn()',mtimetn());

    return $t;
}

function mulshiptime($tm, $quant) {
    return $tm * $quant;

    //	   adddebugval('time',$tm);
    $dst = date('H', 0);
    $tt = $dst * 60 * 60;
    $t = $tm + $tt; //clean time; remove dst
    //	   adddebugval('ct',$t);
    $t *= $quant;
    $t = $t - $tt;  //add dst;
    return $t;
}

//add daylight time saving to time=duration
function adddst($t) {
    $dst = date('H', 0);
    $tt = $dst * 60 * 60;
//    adddebugval("tt",$tt);
    return $tt + $t;
}

//delete daylight time saving to time=duration
function deldst($t) {
    $dst = date('H', 0);
    $tt = $dst * 60 * 60;
//    adddebugval("tt",$tt);
    return $t - $tt;
}

//returns the time to upgrade in days and hours:mins:secs
function gettimetoupgrade($t) {
    $dst = date('H', 0);
    $tt = time_diff_conv(maketimetn($dst, 0, 0) + $t, maketimetn(0, 0, 0));
    // adddebug($tt).'<br>');
    //$dt=date("d", $t)-1;
    // return $dt.'d '.date('H:i:s',$t);
    return $tt;
}

//calculates the time left for an upgrade to finish
function calctimetoupgrade($sttm, $tmdur) {
//     adddebug(time_diff_conv($sttm+$tmdur,mtimetn()).'<br>');
    $t = $sttm + $tmdur - mtimetn();
    return $t;
}

//same as the previous just returns true or false
function istimetoupg($sttm, $tmdur) {
    $t = ($sttm + $tmdur) <= mtimetn();
    return $t;
}

function getformatednumber($n) {
    if (is_null($n) || $n == 'NULL') {
        return 0;
    } else {
        return number_format((float) $n, 0, ',', '.');
    }
}

function getformatednumberwdec($n) {
    if (is_null($n) || $n == 'NULL') {
        return 0;
    } else {
        return number_format((float) $n, 2, ',', '.');
    }
}

function bd_nice_number($n, $lit = true) {
    // first strip any formatting;
    $n = (0 + str_replace(",", "", $n));

    // is this a number?
    if (!is_numeric($n)) {
        return false;
    }

    // now filter it;
    if ($lit) {
        if ($n >= 1000000000000) {
            return round(($n / 1000000000000), 1) . ' trillion';
        } elseif ($n >= 1000000000) {
            return round(($n / 1000000000), 1) . ' billion';
        } elseif ($n >= 1000000) {
            return round(($n / 1000000), 1) . ' million';
        } elseif ($n >= 1000) {
            return round(($n / 1000), 1) . ' thousand';
        }
    } else {
        if ($n >= 1000000000000) {
            return round(($n / 1000000000000), 1) . 't';
        } elseif ($n >= 1000000000) {
            return round(($n / 1000000000), 1) . 'b';
        } elseif ($n >= 1000000) {
            return round(($n / 1000000), 1) . 'm';
        } elseif ($n >= 1000) {
            return round(($n / 1000), 1) . 'k';
        }
    }

    return number_format($n);
}

function getdebugdata(&$r) {
    global $debugdata;
    global $bottomoutput;

    if ($debugdata == null) {
        $debugdata = '';
    }
    $r['debugdata'] = $debugdata;
    if ($bottomoutput == null) {
        $bottomoutput = '';
    }
    $r['debugbottom'] = $bottomoutput;
}

function adddebug($s) {
    global $debugdata;
    $debugdata = $debugdata . $s;
}

function adddebugval($s, $v = '') {
    global $debugdata;
    $debugdata = $debugdata . $s . '=' . $v . '<br>';
}

function addtopoutput($topdata) {
    global $topoutput;
    $topoutput .= $topdata;
}

function addbottomoutput($botdata) {
    global $bottomoutput;
    $bottomoutput .= $botdata . "<BR>";
}

function addoutput($outdata, $outstyle) {
    global $thispg, $thispgstyle;

    $thispg .= $outdata;
    $thispgstyle .= $outstyle;
}

function addjscript($js) {
    global $jscript;

    $jscript .= $js;
}

function getajaxjsfunction($funcname, $funccontent, $funcparams = null) {
    return "
		
		  
		    function $funcname($funcparams)
			{
		  	$funccontent
			}
		  
		  
		";
}

function addjsfunction($funcname, $funccontent, $funcparams = null) {
    global $jscript;

    $jscript .= "
		
		  
		    function $funcname($funcparams)
			{
		  	$funccontent
			}
		  
		  
		";
}

function getjsincludefile($fname, $withurl = false) {
    if ($withurl) {
        $sfname = getBaseUrl();
    } else {
        $sfname = '';
    }



    $sfname .= $fname;
    return "
	      <script type='text/javascript' src='$sfname'></script>";
}

function addstyle($styl) {
    global $bodystyle;

    $bodystyle .= $styl;
}

function addalltherest() {
    global $body, $bodystyle, $jscript, $includedfiles, $thispg, $thispgstyle, $head, $onload;

    $head .= $includedfiles;    //this must be on <head>


    $body .= '
		<script type="text/javascript" >
		
		function myload()		
		{
                    $(".clickmenu").click(function(){
                      window.location=$(this).find("a").attr("href"); 
                      return false;
                    });                

                   ' . $onload . ';	
		}
		
		
		   ' . $jscript . ' 
		</script>
		';
    $body .= $thispg;
    $bodystyle .= $thispgstyle;
}

function addonloadfunction($func) {
    global $onload;

    $onload .= $func;
}

function addincludefile($filename) {
    global $includedfiles;

    if (strpos($includedfiles, (string) $filename) == false) {
        $includedfiles .= $filename;
    }
}

function imageCreateTransparent($x, $y) {
    $imageOut = imagecreate($x, $y);
    $colourBlack = imagecolorallocate($imageOut, 0, 0, 0);
    imagecolortransparent($imageOut, $colourBlack);
    return $imageOut;
}

function ImageGrid(&$im, $startx, $starty, $width, $height, $xcols, $yrows, &$color) {
    for ($x = 0; $x < $xcols; $x++) {
        for ($y = 0; $y < $yrows; $y++) {
            $x1 = $startx + ($width * $x);
            $x2 = $startx + ($width * ($x + 1));
            $y1 = $starty + ($height * $y);
            $y2 = $starty + ($height * ($y + 1));
            ImageRectangle($im, $x1, $y1, $x2, $y2, $color);
        }
    }
}

function createtab($tabarr, $tx, $ty, $tbwidth, $tbheight, $tabselect, &$cont, &$contstyle, $tbsize = 110) {
    global $pg;

    $tabno = count($tabarr);
    $t1 = $tabno * $tbsize;
    $t2 = $tbwidth - $t1;
    $t3 = floor($t2 / 55); //count how many tabs we can have
    $tt = $t1 + $t3 * 55; //at least this size to fit the tabs
    $tsize = max($tt, $tbwidth);
    adddebugval("t1", "$t1");
    adddebugval("t2", "$t2");
    adddebugval("t3", "$t3");
    adddebugval("tt", "$tt");
    adddebugval("tsize", "$tsize");
    $tmpstr = "<DIV class='tabset1'>";
    $tmpstl = ".tabset1  { 
                        display: block;		   
	 		position: absolute;
	 		z-index: 1;
			left: " . $tx . "px;
			top: " . $ty . "px;
                        height: " . $tsize . "px; 
                        width: " . $tsize . "px; 		
		}
		";
    for ($i = 0; $i < $tabno; $i++) {
        $lft = $i * 110;
        $tp = 0;
        if ($i == $tabselect) {
            $img = 'tabsl';
            $col = 'brown';
        } else {
            $img = 'tabns';
            $col = 'blue';
        }
        $tmpstr = $tmpstr . '	
	     	<a href="?pg=' . $pg . '&tab=' . $i . '"><div id="tab_' . $i . '" class="tab_' . $i . '" title="' . $tabarr[$i] . '">
			<div style="font-family:arial;font-size:20px;text-align:center;font-weight:bold;color:' . $col . ';margin-left:05px;margin-top:10px">' . $tabarr[$i] . '
			</div></div> </a>			
		';

        $tmpstl = $tmpstl . ".tab_$i  { 
                        display: block;
                        background-image: url('Images/$img.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: " . $lft . "px;
			top: " . $tp . "px;
                        height: 55px; 
                        width: " . $tbsize . "px; 				
		}
		  ";
    }
    $tp = 36;
    for ($i = 0; $i < $t3; $i++) {
        $lft = $t1 + $i * 55;
        $tmpstr = $tmpstr . '	
	     	<div id="tabn_' . $i . '" class="tabn_' . $i . '">
			</div>
		';

        $tmpstl = $tmpstl . ".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: " . $lft . "px;
			top: " . $tp . "px;
                        height: 20px; 
                        width: 55px; 				
		}
		  ";
    }
    $lft = $t1 + $i * 55;
    $lftover = $tsize - $lft;
    if ($lftover > 0) {
        $tmpstr = $tmpstr . '	
	     	<div id="tabn_' . $i . '" class="tabn_' . $i . '">
			</div>
		';

        $tmpstl = $tmpstl . ".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: " . $lft . "px;
			top: " . $tp . "px;
                        height: 20px; 
                        width: " . $lftover . "px; 				
		}
		  ";
    }



    $lft = 0;
    $tp = 53;
    $theight = $tbheight - $tp;
    $tmpstr = $tmpstr . "<DIV class='tabset_in'>$cont</DIV>";
    $tmpstl = $tmpstl . $contstyle;
    $tmpstl = $tmpstl . ".tabset_in  { 
                        display: block;
                        background-image: url('Images/tabbg.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: " . $lft . "px;
			top: " . $tp . "px;
                        height: " . $theight . "px; 
                        width: " . $tsize . "px; 		
		}
		";

    $tmpstr = $tmpstr . "</DIV>";


    $cont = $tmpstr;
    $contstyle = $tmpstl;
    //addoutput($tmpstr,$tmpstl);
}

//remove a query variable
function remove_querystring_var($url, $key) {
    $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
    //		$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
    $url = substr($url . 'a', 0, -1);
    return $url;
}

function curPageURL() {
    $pageURL = 'http';
    //if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/**
 * Suppose, you are browsing in your localhost
 * http://localhost/myproject/index.php?id=8
 */
function getBaseUrl() {
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];

    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);

    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];

    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'http://';

    // return: http://localhost/myproject/
    return $protocol . $hostName . $pathInfo['dirname'] . "/";
}

function swap(&$a, &$b) {
    $t = $b;
    $b = $a;
    $a = $t;
}

function dbgshowarray($arr) {
    $arrcnt = count($arr);
    for ($i = 0; $i < $arrcnt; $i++) {
        $x = $arr[$i]['x'];
        $y = $arr[$i]['y'];
        adddebugval("$i. x,y", $x . ':' . $y);
    }
}

function reversearray(&$arr) {
    adddebug('should reverse<br>');
    $arrcnt = count($arr);
    $ni = 0;
    for ($i = $arrcnt - 1; $i >= 0; $i--) {
        $newarr[$ni++] = $arr[$i];
    }
    $arr = $newarr;
}

function addroutepoint(&$arr, $x, $y) {
    if (myisset($arr)) {
        $arrcnt = count($arr);
        $i = $arrcnt;
    } else {
        $i = 0;
    }
    $arr[$i]['x'] = $x;
    $arr[$i]['y'] = $y;

    //adddebugval("$i. x,y",$x.':'.$y);
}

function get_line($x1, $y1, $x2, $y2) {
    //	adddebug("from $x1,$y1<br>");
    //	adddebug("to   $x2,$y2<br>");

    $points = null;
    $issteep = abs($y2 - $y1) > abs($x2 - $x1);
    if ($issteep) {
        swap($x1, $y1);
        swap($x2, $y2);
    }
    $rev = false;
    if ($x1 > $x2) {
        swap($x1, $x2);
        swap($y1, $y2);
        $rev = true;
    }
    $deltax = $x2 - $x1;
    $deltay = abs($y2 - $y1);
    $error = floor($deltax / 2);
    $y = $y1;
//    $ystep = NULL;
    if ($y1 < $y2) {
        $ystep = 1;
    } else {
        $ystep = -1;
    }
    //	adddebug("$x1 to $x2<br>");
    for ($x = $x1; $x < $x2 + 1; $x++) {
        if ($issteep) {
            addroutepoint($points, $y, $x);
        } else {
            addroutepoint($points, $x, $y);
        }
        $error -= $deltay;
        if ($error < 0) {
            $y += $ystep;
            $error += $deltax;
        }
    }
    # Reverse the list if the coordinates were reversed
    if ($rev) {
        reversearray($points);
    }
    dbgshowarray($points);
    return $points;
}

function myisset($tochk) {
    if ($tochk !== null and $tochk !== false) {
        return true;
    } else {
        return false;
    }
}

function getsessionvar($varname) {
    if (is_array($varname)) {
        adddebug("SESSIONVAR IS ARRAY<br>");
        return null;
    }

    if ($varname != null and $varname != '' and array_key_exists($varname, $_SESSION)) {
        return $_SESSION[$varname];
    } else {
        return null;
    }
}

function getrequestvar($varname) {
    if (array_key_exists($varname, $_REQUEST)) {
        return $_REQUEST[$varname];
    } else {
        return null;
    }
}

function getarrayvalue($arr, $idx) {
    if (!is_array($arr)) {
        adddebug("Not an array $arr<br>");
        return -1;
    }

    if (array_key_exists($idx, $arr)) {
        return $arr[$idx];
    } else {
        return 0;
    }
}
