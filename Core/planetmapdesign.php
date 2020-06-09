<?php
    include_once "common.php";
    include_once("galaxyutils.php");



   //map planet coords
   function getplanetmapcoords($x, $y, &$scx, &$scy)
   {
       global $solsyssize;
      
       $solx=$_SESSION['solx'];
       $soly=$_SESSION['soly'];

       
//     $sx=floor($stx + $x / $solsyssize);
       //	 $sy=floor($sty + $y / $solsyssize);
       //	 $scx= $sx * $solsyssize + $x % $solsyssize;
       //	 $scy= $sy * $solsyssize + $y % $solsyssize;

       $scx=$solx*$solsyssize+$x;
       $scy=$soly*$solsyssize+$y;
     
       //	 echo "x=$x".'<br>';
//	 echo "y=$y".'<br>';
//	 echo "sx=$sx".'<br>';
//	 echo "sy=$sy".'<br>';
//	 echo "scx=$scx".'<br>';
//	 echo "scy=$scy".'<br>';
//	 echo "-----------".'<br>';
   }


   function placeplanet($x, $y)
   {
       global $solarr,$dbmaparr,$tilesizex,$tilesizey,$planetstr,$planetstyl;
       global $mapoffsetx,$mapoffsety;
    
    
    
       getplanetmapcoords($x, $y, $scx, $scy);
       //	 echo "scx=$scx".'<br>';
       //	 echo "scy=$scy".'<br>';
       //check to see if there is a planet at scx,scy coords
       //	 echo 'ID=['.$solarr[$scx][$scy].']<br>';
     
       $rx=$mapoffsetx+$x*$tilesizex*3;
       $ry=$mapoffsety+$y*$tilesizey*3;
       $ry-=128;
      
       if (myisset($solarr[$scx][$scy])) {
           //placeplanet
           $id=$solarr[$scx][$scy];
           $tid='quadr_'.$id;
           $plname=$dbmaparr[$id]['name'];
           $hint = "$plname ($scx:$scy)";
       
           $img=$dbmaparr[$id]['imagename'].'48.png';
           $tmppl= '
	     <a href="?pg=planet&selplanet='.$id.'"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
	   ';
           $tsx=$tilesizex*3;
           $tsy=$tilesizey*3;
           $tmpst="
	   .quadr_$id  { display: block;
    background-image: url('Images/$img') ;
	 position: absolute;
	 z-index: 0;
	left: ".$rx."px;
	top: ".$ry."px;
    height: ".$tsx."px; 
    width: ".$tsy."px; 
		} 
		";
        
           //echo 'PLAN=[ '.$tmppl.' ]';
           //echo 'styl=[ '.$tmpst.' ]';
           $planetstr=$planetstr.$tmppl;
           $planetstyl=$planetstyl.$tmpst;
       } else {
           //place void
       }
   }


   function placeselected()
   {
       global $planetstr,$planetstyl,$tilesizex,$tilesizey,$dbmaparr;
       global $solsyssize,$mapoffsety,$mapoffsetx;
       
       
       $selplanet=$_SESSION['selplanet'];
       $solx=$_SESSION['solx'];
       $soly=$_SESSION['soly'];
    

       
       if (myisset($selplanet)) {
           $x= $dbmaparr[$selplanet]['coordx']-$solx*$solsyssize;
           $y= $dbmaparr[$selplanet]['coordy']-$soly*$solsyssize;
           $rx=$mapoffsetx+$x*$tilesizex*3;
           $ry=$mapoffsety+$y*$tilesizey*3;
           $ry-=128;
           $rx=$rx-5;
           $ry=$ry-6;//adjust for select image
           adddebugval('SolSys Offset', "$solx:$soly");
           adddebugval('Map Quadrant', "$x:$y");
           adddebugval('Map Coords', "$rx:$ry");
           //echo "SolSys Offset:$solx:$soly Map Quadrant:$x:$y Map Coords:$rx:$ry<br>";
           adddebugval('Selected planet', "$selplanet");
           //echo "Selected planet=$selplanet<br>";

           $tid='selectfrom';
           $hint = "Selected ";
       
           $img='selectplanet58.png';
       
           $tmppl= '
	     <a href="'.$selplanet.'"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
	   ';
           $tmpst="
	   .$tid  { display: block;
    background-image: url('Images/$img') ;
	 position: absolute;
	 z-index: 1;
	left: ".$rx."px;
	top: ".$ry."px;
    height: 58px; 
    width: 58px; 

		} 
		";
        
           //	echo 'PLAN=[ '.$tmppl.' ]';
           //	echo 'styl=[ '.$tmpst.' ]';
           $planetstr.=$tmppl;
           $planetstyl.=$tmpst;
       }
   }
   


    function showsolarsystem()
    {
        global $solarr,$solsyssize,$dbmaparr;

        $selplanet=$_SESSION['selplanet'];
     
        $dbarray = getplanetinfo($selplanet);
        $solx=$dbarray['solsysx'];
        $soly=$dbarray['solsysy'];
        $_SESSION['solx']=$solx;
        $_SESSION['soly']=$soly;
        
        //echo "SOLXY=$solx:$soly<br>";
        adddebugval('SOLXY', "$solx:$soly");
        
        //		$qur='SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`)';
        //		$qur.=" and (solsysx=$solx) and (solsysy=$soly)";
        //		$qres=query_exec($qur);
        //		$qrows=query_num_rows($qres);
        $qres=getsolsysplanets($solx, $soly, $qrows);
        

        
        for ($i=0;$i<$qrows;$i++) {
            $dbarray = query_fetch_array($qres);
            $mx=$dbarray['coordx'];
            $my=$dbarray['coordy'];
            $code=$dbarray['pid'];
            $solarr[$mx][$my]=$code;
            $dbmaparr[$code]=$dbarray;
        }

        adddebugval("setplanet", "$solsyssize");
        for ($x=0; $x<$solsyssize; $x++) {
            for ($y=0; $y<$solsyssize; $y++) {
                placeplanet($x, $y);
            }
        }
        placeselected();
    }


    function showgrid()
    {
        global $planetstr,$planetstyl;
        $tmppl= "
         <img class='gridimg' src='Core/gridplanetpng.php'>
	   ";
        $tmpst="
	.gridimg
	{
      position: absolute;
      display: block;
      z-index: -1;
      top: 0px;
      left: 00px;	
	}
	
	";
        $planetstr=$planetstr.$tmppl;
        $planetstyl=$planetstyl.$tmpst;
    }

    function getplanetmap($isajax=false)
    {
        global $planetstr,$planetstyl;
    
    
        $planetstr='';
        $planetstyl='';
        showsolarsystem();
        showgrid();

        if (!$isajax) {
            addoutput($planetstr, $planetstyl);
        } else {
            return "<style type='text/css'>".$planetstyl."</style>".$planetstr;
        }
    }
