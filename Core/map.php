<?php

   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once("gamesession.php");
   include_once("mapplanetlist.php");
   include_once("mapfleetlist.php");
   include_once("maproutelist.php");

   include_once("srvendturn.php");
   
   global $stx,$sty,$planetstr,$planetstyl;
   global $ssx,$ssy,$selplanet,$galaxysize,$userid;



   
   //map planet coords
   function getplanetcoords($x, $y, &$scx, &$scy)
   {
       global $solsyssize,$stx,$sty;
       
       $sx=floor($stx + $x / $solsyssize);
       $sy=floor($sty + $y / $solsyssize);
       $scx= $sx * $solsyssize + $x % $solsyssize;
       $scy= $sy * $solsyssize + $y % $solsyssize;
     
     
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
       global $maparr,$dbmaparr,$tilesizex,$tilesizey,$planetstr,$planetstyl;
       global $mapoffsetx,$mapoffsety,$userid;
    
       getplanetcoords($x, $y, $scx, $scy);
       //echo "scx=$scx".'<br>';
       //echo "scy=$scy".'<br>';
       //check to see if there is a planet at scx,scy coords
       //echo 'ID=['.$maparr[$scx][$scy].']<br>';
     
       $rx=$mapoffsetx+$x*$tilesizex;
       $ry=$mapoffsety+$y*$tilesizey;
     
       if (myisset($maparr[$scx][$scy])) {
           //placeplanet
           $id=$maparr[$scx][$scy];
           $tid='quadr_'.$id;
           $pname=$dbmaparr[$id]['name'];
           getplanetowner($id, $ownerid, $ownername);
       
           $hint = "$pname [$scx:$scy] ($ownername)";
       
           $img=$dbmaparr[$id]['imagename'].'16.png';
       
           if (selectfleetmode()) {
               $tmppl= '
	     <div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> 
	   ';
           } else {
               $tmppl= '
	     <a href="?pg=map&selplanet='.$id.'"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
	   ';
           }
       
           if (selectfleetmode()) {
               $zi=0;
           } else {
               $zi=1;
           }
       
           $tmpst="
	   .quadr_$id  { display: block;
    background-image: url('Images/$img') ;
	 position: absolute;
	 z-index: $zi;
	left: ".$rx."px;
	top: ".$ry."px;
    height: ".$tilesizex."px; 
    width: ".$tilesizey."px; 
		} 
		";
           //mark user and enemy planets
           $pown=$dbmaparr[$id]['ownerid'];

           if ($pown>0) {
               if ($pown!=$userid) {
                   $img='enemyplanet24.png';
               } else {
                   $img='userplanet24.png';
               }
               $rx=$rx-4;
               $ry=$ry-4;
               $tmppl=$tmppl. '
	        <div id="mine_'.$id.'" class="mine_'.$id.'" ></div> 
	        ';
               $tmpst=$tmpst."
	   				.mine_$id  { 
						display: block;
    					background-image: url('Images/$img') ;
	 					position: absolute;
	 					z-index: -1;
						left: ".$rx."px;
						top: ".$ry."px;
    					height: 24px; 
    					width: 24px; 
					} 
				";
           }
        
           //echo 'PLAN=[ '.$tmppl.' ]';
           //echo 'styl=[ '.$tmpst.' ]';
           $planetstr=$planetstr.$tmppl;
           $planetstyl=$planetstyl.$tmpst;
       } else {
           //place void
       }
   }
   
   function placeselectedplanet()
   {
       global $stx,$sty,$planetstr,$planetstyl,$selplanet,$tilesizex,$tilesizey,$dbmaparr;
       global $solsyssize,$mapoffsety,$mapoffsetx;
       
       if (myisset($selplanet) and array_key_exists("$selplanet", $dbmaparr)) {
           $rx= $dbmaparr[$selplanet]['coordx'];
           $ry= $dbmaparr[$selplanet]['coordy'];
           normalizecoords($rx, $ry);
           $rx=$rx-4;
           $ry=$ry-4;//adjust for select image
           adddebugval("SolSys Offset", "$stx");
           adddebugval("Map Coords", "$rx:$ry");
           //	  echo "SolSys Offset:$stx:$sty Map Quadrant:$x:$y Map Coords:$rx:$ry<br>";
        
           getplanetowner($selplanet, $ownerid, $ownername);
           $tid='selectfrom';
           $hint = "Selected $rx,$ry ($ownername) ";
       
           $img='selectplanet24.png';
           if (selectfleetmode()) {
               $tmppl= '
	       <div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div>
	     ';
               $zi=1;
           } else {
               $tmppl= '
	       <a href="?pg=planet"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
	     ';
               $zi=2;
           }
           $tmpst="
	   .$tid  { display: block;
    background-image: url('Images/$img') ;
	 position: absolute;
	 z-index: $zi;
	left: ".$rx."px;
	top: ".$ry."px;
    height: 24px; 
    width: 24px; 
		} 
		";
        
           //	echo 'PLAN=[ '.$tmppl.' ]';
           //	echo 'styl=[ '.$tmpst.' ]';
           $planetstr=$planetstr.$tmppl;
           $planetstyl=$planetstyl.$tmpst;
       }
   }
   
   
   
   function showplanets()
   {
       global $solsyssize,$stx,$sty,$maparr,$dbmaparr;
       // global $galaxysize,$ssx,$ssy;
       //'SELECT * FROM `planets`,`planettypes` WHERE `planets`.`typeid` = `planettypes`.`id`';
       //center on ssx,ssy
       //$adnx=0;$adny=0;
       //if (($ssx-1)>=0) $stx=$ssx-1; else {$stx=0;$adnx=1;}
       //if (($ssy-1)>=0) $sty=$ssy-1; else {$sty=0;$adny=1;}
       //if (($ssx+1)<$galaxysize) $sex=$ssx+1+$adnx; else $sex=$galaxysize-1;
       //if (($ssy+1)<$galaxysize) $sey=$ssy+1+$adny; else $sey=$galaxysize-1;
       getvisiblemap($stx, $sty, $sex, $sey);

       $qur='SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`)';
       $qur=$qur." and (solsysx>=$stx) and (solsysy>=$sty) and (solsysx<=$sex) and (solsysy<=$sey)";
       //echo $qur;
       $qres=query_exec($qur);
       $qrows=query_num_rows($qres);

       //put in an array
       //echo "ROWS=$qrows<br>";
       for ($i=0;$i<$qrows;$i++) {
           $dbarray = query_fetch_array($qres);
           $mx=$dbarray['coordx'];
           $my=$dbarray['coordy'];
           $code=$dbarray['pid'];
           //echo 'Set '.$mx.','.$my.'='.$code.'<br>';
           $maparr[$mx][$my]=$code;
           $dbmaparr[$code]=$dbarray;
           //echo $maparr[$mx][$my].' set<br>';
       }
        
        
       //show map;
        
       //		$x=0;$y=0;//planets x,y
       // show 3x3 solar systems
       //		echo 'solsyssize='.$solsyssize.'<br>';
       adddebugval("solsyssize", "$solsyssize");
       for ($x=0; $x<$solsyssize*3; $x++) {
           for ($y=0; $y<$solsyssize*3; $y++) {
               placeplanet($x, $y);
           }
       }
        
       placeselectedplanet();
   }
    
    function showmapmenu()
    {
        global $planetstr,$planetstyl;
        $upx=430;
        $upy=640;
        $dnx=$upx+2;
        $dny=$upy+32+3;
        $lex=$upx-32-3;
        $ley=$upy+16;//  32/2
        $rix=$upx+32+3;
        $riy=$ley;
    
        $strtx=15;
        $strty=640;

        if (myisset($_SESSION['route'])) {
            $rttitle='Select destination or click to cancel route';
            $rtimage='routeen48';
        } else {
            $rtimage='routest48';
            $rttitle='start route';
        }
        
        
        $tmppl= "
  <div class='mapmenu'>
     <a href='index.php?pg=map&action=stroute' ><img class='stroute' src='Images/$rtimage.png' title='$rttitle'> </a>  
     <a href='index.php?pg=map&action=mapup' ><img class='arrowup' src='Images/arrowup.png'> </a>
     <a href='index.php?pg=map&action=mapdn' ><img class='arrowdn' src='Images/arrowdn.png'> </a>
     <a href='index.php?pg=map&action=maple' ><img class='arrowle' src='Images/arrowle.png'> </a>
     <a href='index.php?pg=map&action=mapri' ><img class='arrowri' src='Images/arrowri.png'> </a>
	 </div>";
        $tmpst="
	.stroute
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: $strty"."px;
      left: $strtx"."px;	
	}	
	.arrowup
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: $upy"."px;
      left: $upx"."px;	
	}
	.arrowdn
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: $dny"."px;
      left: $dnx"."px;	
	}
	.arrowle
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: $ley"."px;
      left: $lex"."px;	
	}
	.arrowri
	{
      position: absolute;
      display: block;
      z-index: 2;
      top: $riy"."px;
      left: $rix"."px;	
	}
	
	
	";

        $planetstr=$planetstr.$tmppl;
        $planetstyl=$planetstyl.$tmpst;
    }
    
    function validmapclick($x, $y)
    {
        global $mapoffsetx,$mapoffsety,$solsyssize,$tilesizex;

        $solsyswidth=$solsyssize*$tilesizex;
        $lx=$mapoffsetx ;
        $rx=$lx+$solsyswidth*3;
        $ty=$mapoffsety-128;
        $by=$ty+$solsyswidth*3;
        return $x>$lx and $x<$rx and $y>$ty and $y<$by;
    }
    
    function normalizemapxy(&$x, &$y, &$tx, &$ty)
    {
        global $mapoffsetx,$mapoffsety,$solsyssize,$tilesizex,$ssx,$ssy;
        
        $f=validmapclick($x, $y);
        if ($f) {
            $x=$x-$mapoffsetx;
            $y=$y-$mapoffsety+128;
            $tx=floor($x / $tilesizex);
            $ty=floor($y / $tilesizex);
            //	adddebugval('tx',$tx);
//    adddebugval('ty',$ty);
            
            $sox=max(0, $ssx-1);
            $soy=max(0, $ssy-1);
            //	adddebugval('offs x',$sox);
//    adddebugval('offs y',$soy);
            $tx+=($sox*$solsyssize);
            $ty+=($soy*$solsyssize);
        }
        
        return $f;
    }
    
    function showgrid()
    {
        global $planetstr,$planetstyl;
    
    
    
        $tmppl= "
	  <div class='mainmap'>
	";
    
        if (selectfleetmode()) {
            $tmppl.= "		
		<form action='' method='post'>
			<input type='image' src='Core/gridpng.php'
				name='map' style='z-index:10;cursor:crosshair;'/>
</form>
		";
        } else {
            $tmppl.= "
	  
         <img class='gridimg' src='Core/gridpng.php'>
	   ";
        }

        $tmppl.= "</div>";
        $tmpst="
	.gridimg
	{
      position: absolute;
      display: block;
      z-index: -1;
      top: 0px;
      left: 00px;	
	}
	.mainmap
	{
	 top:128;
	 position:absolute;	
	 width:520;
	 height:520;	
	}
	";

        $planetstr=$planetstr.$tmppl;
        $planetstyl=$planetstyl.$tmpst;
    }
    
    function validcoords($x, $y)
    {
        global $mapoffsety,$mapoffsetx,$solsyssize,$tilesizex;
        
        $solsyswidth=$solsyssize*$tilesizex;
        $mapwidth=$solsyswidth*3;
        
        $f1=($x>$mapoffsetx) and ($x<$mapwidth+$mapoffsetx);
        $f2=($y>$mapoffsety) and ($y<$mapwidth+$mapoffsety);
      
        return $f1 and $f2;
    }
    
    function placefleet($farr)
    {
        global $mapoffsetx,$mapoffsety,$tilesizex,$tilesizey;
        global $stx,$sty,$solsyssize,$userid;
        
        $fcx=$farr['coordx'];
        $fcy=$farr['coordy'];
        $fltid=$farr['fltid'];
        $fltname=$farr['fltname'];
        $owner=$farr['ownerid'];
        $ownername=getusername($owner);
        $tid='fleet_'.$fltid;
        $hint="$fltname ($ownername)";
        
        $rx=$fcx;
        $ry=$fcy;
        normalizecoords($rx, $ry);
        if (validcoords($rx, $ry)==false) {
            return false;
        }

        // adddebugval('rx',$rx);
        // adddebugval('ry',$ry);
        if ($owner!=$userid) {
            $img='fleet16red.png';
        } else {
            $img='fleet16aqua.png';
        }

        if (selectfleetmode()) {
            $tmppl= '
	          <div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div>
	    	';
            $zi=0;
        } else {
            $tmppl= '
		     <a href="?pg=map&selfleet='.$fltid.'"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
	   		';
            $zi=2;
        }
        $tmpst="
	   .$tid { display: block;
    background-image: url('Images/$img') ;
	 position: absolute;
	 z-index: $zi;
	left: ".$rx."px;
	top: ".$ry."px;
    height: 16px; 
    width: 16px; 
		} 
		";
        
        addoutput($tmppl, $tmpst);
    }
    
    function normalizecoords(&$cx, &$cy)
    {
        global $stx,$sty,$tilesizex,$tilesizey;
        global $solsyssize,$mapoffsety,$mapoffsetx;
        
        $x= $cx-$stx*$solsyssize;//quadr x
      $y= $cy-$sty*$solsyssize;//quadr y
      $rx=$mapoffsetx+$x*$tilesizex;
        $ry=$mapoffsety+$y*$tilesizey;
        
        $cx=$rx;
        $cy=$ry;
    }
    
   function placeselectedfleet($selfleet)
   {
       if (myisset($selfleet)) {
           $qres=getfleetbyid($selfleet);
           $reccnt=query_num_rows($qres);
           if ($reccnt>0) { //we have valid fleet
               $dbarr=query_fetch_array($qres);
               $fcx=$dbarr['coordx'];
               $fcy=$dbarr['coordy'];
               $rx=$fcx;
               $ry=$fcy;
               normalizecoords($rx, $ry);
               $rx=$rx-4;
               $ry=$ry-4;//adjust for select image

               //	      adddebugval("fltsel Coords","$rx:$ry");
               //	  echo "SolSys Offset:$stx:$sty Map Quadrant:$x:$y Map Coords:$rx:$ry<br>";

               $tid='selectfleet';
               $hint = "Selected [$rx,$ry] ";
       
               $img='selectfleet24.png';
       
               $tmppl= '
	           <a href="?pg=map"><div id="'.$tid.'" class="'.$tid.'" title="'.$hint.'"></div> </a>
      	   ';
               $tmpst="
         	   .$tid  { display: block;
                        background-image: url('Images/$img') ;
						position: absolute;
	 					z-index: 1;
						left: ".$rx."px;
						top: ".$ry."px;
    					height: 24px; 
    					width: 24px; 
					} 
				";
        
               addoutput($tmppl, $tmpst);
           }
       }
   }
   
    
    
    function showfleetsonmap()
    {
        global $solsyssize;
        getvisiblemap($tx, $ty, $bx, $by);
        adddebugval('solsyssize', $solsyssize);
        $qres=getalluserfleets(null, $tx*$solsyssize, $ty*$solsyssize, ++$bx*$solsyssize, ++$by*$solsyssize);
        if (myisset($qres)) {
            $reccnt=query_num_rows($qres);
           // adddebugval('reccnt', $reccnt);
            if ($reccnt>0) { //we have visible fleets
                for ($i=0;$i<$reccnt;$i++) {
                    $dbarr=query_fetch_array($qres);
                    placefleet($dbarr);
                }
            }
        
            if (myisset(filter_input(INPUT_GET, 'selfleet'))) {
                $selfleet=filter_input(INPUT_GET, 'selfleet');
                $_SESSION['selfleet']=$selfleet;
            } elseif (myisset($_SESSION['selfleet'])) {
                $selfleet=$_SESSION['selfleet'];
            } else {
                $selfleet=null;
            }

            placeselectedfleet($selfleet);
        }
    }
    
    
    function showplayerstuff(&$tabcont, &$tabcontstyle)
    {
        
        //test tabs
        $tabarr[0]='PLANETS';
        $tabarr[1]='FLEETS';
        $tabarr[2]='ROUTES';
        //		$tabarr[3]='FLEETS';


        if (myisset(filter_input(INPUT_GET, 'tab'))) {
            $tbsel=filter_input(INPUT_GET, 'tab');
        } else {
            if (myisset($_SESSION['$maptab'])) {
                $tbsel=$_SESSION['$maptab'];
            } else {
                $tbsel=0;
            }
        }
        $_SESSION['$maptab']=$tbsel;
       
       
        switch ($tbsel) {
         case 0:
            getplanetlist($tabcontent, $tabcontentstyle);
         
         break;
         case 1:
            getfleetlist($tabcontent, $tabcontentstyle);
         
         break;
         case 2:
            getroutelist($tabcontent, $tabcontentstyle);
         
         break;
         case 3:
            $tabcontent="TODO:show planet fleets";$tabcontentstyle="";
         
         break;
         
         default:
          $tabcontent="";$tabcontentstyle="";
        }
        
        createtab($tabarr, 520, 140, 500, 600, $tbsel, $tabcontent, $tabcontentstyle);

        $tabcont.=$tabcontent;
        $tabcontstyle.=$tabcontentstyle;
    }
    

    function checkaddroute()
    {
        if (myisset($_SESSION['route'])) {
            if (myisset(filter_input(INPUT_POST, 'map_x'))) {
                $clk_x=filter_input(INPUT_POST, 'map_x');
            }
            if (myisset(filter_input(INPUT_POST, 'map_x'))) {
                $clk_y=filter_input(INPUT_POST, 'map_y');
            }
            //  adddebugval('clk x',$clk_x);
            //  adddebugval('clk y',$clk_y);
            if (normalizemapxy($clk_x, $clk_y, $tx, $ty)) {
                adddebug("valid<br>");
                $fltid=$_SESSION['selfleet'];
                adddebug("add route for fleet id=$fltid to $tx:$ty<br>");
                addroute($fltid, $tx, $ty);
            } else {
                adddebug("not valid<br>");
            }
            //		  adddebugval('clk x',$clk_x);
//    	  adddebugval('clk y',$clk_y);
//		  adddebugval('tile x',$tx);
//    	  adddebugval('tile y',$ty);
        }
    }


   
   //Default page commands


    if (myisset($_SESSION['curplpage'])) {
        $cp=$_SESSION['curplpage'];
    }
    if (myisset($_SESSION['maxplpage'])) {
        $maxpg=$_SESSION['maxplpage'];
    }
  
   //echo "gsize=$galaxysize ssx=$ssx";
   if (myisset(filter_input(INPUT_GET, 'action'))) {
       $act=filter_input(INPUT_GET, 'action');
       switch ($act) {
     case "mapup":
        $ssy--;
        break;
     case "mapdn":
        $ssy++;
        break;
     case "maple":
        $ssx--;
        break;
     case "mapri":
        $ssx++;
        break;
     case "plistle":
        if ($cp>1) {
            $cp--;
        }
        $_SESSION['curplpage']=$cp;
        break;
     case "plistri":
        if ($cp<$maxpg) {
            $cp++;
        }
        $_SESSION['curplpage']=$cp;
        break;
     case "stroute":
         if (!myisset($_SESSION['selfleet'])) {
             $t=$_SERVER['QUERY_STRING'] ;
             header("Location: index.php?$t&msg='Select a fleet first!!!' ");
         } elseif (myisset($_SESSION['route']) and !myisset(filter_input(INPUT_POST, 'map_x'))) {
             $_SESSION['route']=null;
         } else {
             $_SESSION['route']='en';
         }
        break;

    }
       if ($ssx>$galaxysize-2) {
           $ssx=$galaxysize-2;
       }
       if ($ssx<1) {
           $ssx=1;
       }
       if ($ssy>$galaxysize-2) {
           $ssy=$galaxysize-2;
       }
       if ($ssy<1) {
           $ssy=1;
       }
   }
   $_SESSION['ssx']=$ssx;
   $_SESSION['ssy']=$ssy;


   $planetstr='';$planetstyl='';
   if (myisset(filter_input(INPUT_GET, 'selplanet'))) {
       $selplanet=filter_input(INPUT_GET, 'selplanet');
       $_SESSION['selplanet']=$selplanet;
       if (myisset(filter_input(INPUT_GET, 'action'))) {
           if (filter_input(INPUT_GET, 'action')='center') {
               centertoplanet($selplanet);
           }
       }
       adddebug("Planet selected<br>");
   } elseif (!myisset($selplanet)) {
       adddebug("Setting default selected planet<br>");
       $selplanet=getusercapitol($userid);
       $_SESSION['selplanet']=$selplanet;
       centertoplanet($selplanet);
       // adddebugval("ssx",$ssx);
// 	 adddebugval("ssy",$ssy);
   }
 //  else if (myisset($selplanet)){
// 	 adddebug("Planet selected:$selplanet<br>");
//	 getplanetsolarsystem($selplanet,$ssx,$ssy);
//   $_SESSION['ssx']=$ssx;
//   $_SESSION['ssy']=$ssy;
//	 adddebugval("ssx",$ssx);
// 	 adddebugval("ssy",$ssy);
//   }
   else {
       adddebug("$selplanet no planet selected<br>");
     
       $_SESSION['ssx']=$ssx;
       $_SESSION['ssy']=$ssy;
   }
   

    
    adddebugval("Galaxy size", $galaxysize);
    adddebugval("Solar system to center", "($ssx,$ssy)");
    //echo '<br>Galaxy size='.$galaxysize.'<br>';
//	echo "Solar system to center:($ssx,$ssy)<br>";

    checkaddroute();
    
    if (myisset($selplanet)) {
        doendturn($selplanet);
    } else {
        doendturn();
    }
    
    
    showplanets();
    showfleetsonmap();
    showgrid();
    showmapmenu();

    showplayerstuff($planetstr, $planetstyl);
    


    addoutput($planetstr, $planetstyl);
