
<?php
   include_once("gamesession.php");
   include_once("galaxyutils.php");
   
   include_once("planetprod.php"); //set production
   include_once("planetfleet.php"); //show planet ships
   include_once("planetship.php"); //set ships to build
   include_once("planetqueue.php"); //show build queue
      
   include_once("myutils.php");
   include_once("shipbuild.php");
   
   include_once("srvendturn.php");
   




   //map planet coords
   function getplanetcoords($x, $y, &$scx, &$scy)
   {
       global $solsyssize,$stx,$sty,$solx,$soly;
       
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
       getplanetcoords($x, $y, $scx, $scy);
       //	 echo "scx=$scx".'<br>';
       //	 echo "scy=$scy".'<br>';
       //check to see if there is a planet at scx,scy coords
       //	 echo 'ID=['.$solarr[$scx][$scy].']<br>';
     
       $rx=$mapoffsetx+$x*$tilesizex*3;
       $ry=$mapoffsety+$y*$tilesizey*3;
     
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
       global $planetstr,$planetstyl,$selplanet,$tilesizex,$tilesizey,$dbmaparr;
       global $solx,$soly,$solsyssize,$mapoffsety,$mapoffsetx;
       
       if (myisset($selplanet)) {
           $x= $dbmaparr[$selplanet]['coordx']-$solx*$solsyssize;
           $y= $dbmaparr[$selplanet]['coordy']-$soly*$solsyssize;
           $rx=$mapoffsetx+$x*$tilesizex*3;
           $ry=$mapoffsety+$y*$tilesizey*3;
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
           $planetstr=$planetstr.$tmppl;
           $planetstyl=$planetstyl.$tmpst;
       }
   }
   


    function showsolarsystem()
    {
        global $solx,$soly,$solarr,$solsyssize,$dbmaparr,$selplanet;

     
        $qur="select * from `planets` where `pid`=$selplanet";
        $qres=query_exec($qur);
        $dbarray = query_fetch_array($qres);
        $solx=$dbarray['solsysx'];
        $soly=$dbarray['solsysy'];
        $_SESSION['solx']=$solx;
        $_SESSION['soly']=$soly;
        
        //echo "SOLXY=$solx:$soly<br>";
        adddebugval('SOLXY', "$solx:$soly");
        
        $qur='SELECT * FROM `planets`,`planettypes` WHERE (`planets`.`typeid` = `planettypes`.`ptypeid`)';
        $qur.=" and (solsysx=$solx) and (solsysy=$soly)";
        $qres=query_exec($qur);
        $qrows=query_num_rows($qres);

        
        for ($i=0;$i<$qrows;$i++) {
            $dbarray = query_fetch_array($qres);
            $mx=$dbarray['coordx'];
            $my=$dbarray['coordy'];
            $code=$dbarray['pid'];
            $solarr[$mx][$my]=$code;
            $dbmaparr[$code]=$dbarray;
        }
        //echo"setplanet:$solsyssize";
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
      top: 128px;
      left: 00px;	
	}
	
	";
        $planetstr=$planetstr.$tmppl;
        $planetstyl=$planetstyl.$tmpst;
    }
    
    
    

    function showplanettabs(&$tabcont, &$tabcontstyle)
    {
        global $selplanet;
        
        //test tabs
        $tabarr[0]='PROD';
        $tabarr[1]='SHIPS';
        $tabarr[2]='BUILD';
        $tabarr[3]='QUEUE';
        
        if (myisset(filter_input(INPUT_GET, 'tab'))) {
            $tbsel=filter_input(INPUT_GET, 'tab');
        } else {
            if (myisset($_SESSION['$planettab'])) {
                $tbsel=$_SESSION['$planettab'];
            } else {
                $tbsel=0;
            }
        }
        $_SESSION['$planettab']=$tbsel;
        
        $tabcontent='';
        $tabcontentstyle='';
        switch ($tbsel) {
         case 0:
            getprodforplanet($tabcontent, $tabcontentstyle, $selplanet);
         
         break;
         case 1:
            getplanetfleet($tabcontent, $tabcontentstyle, $selplanet);
         break;
         
         case 2:
            getshipsforplanet($tabcontent, $tabcontentstyle, $selplanet);
         break;
         case 3:
            getshipqueueforplanet($tabcontent, $tabcontentstyle, $selplanet);

        
         break;
         
         default:
          $tabcontent="";$tabcontentstyle="";
        }

        createtab($tabarr, 515, 140, 505, 622, $tbsel, $tabcontent, $tabcontentstyle);
        $tabcont.=$tabcontent;
        $tabcontstyle.=$tabcontentstyle;
    }


    function showplanetinfo()
    {
        global $selplanet,$dbmaparr;
        $dbarr=$dbmaparr[$selplanet];
        
        getplanetowner($selplanet, $uid, $uname);
        if ($uid==0) {
            $uname="NoOne";
        }
        //		  <img class='imgplaninfo' src='Images/infobg.png'>

        $gp=$dbarr['goldprod'];
        $mp=$dbarr['metalumprod'];
        $tp=$dbarr['tritiumprod'];
        getplanethourprod($selplanet, $gp, $mp, $tp);
        $r1=$gp-$dbarr['goldprod'];
        $r2=$mp-$dbarr['metalumprod'];
        $r3=$tp-$dbarr['tritiumprod'];
        $plname=$dbarr['name'];
        $coords=$dbarr['coordx'].':'.$dbarr['coordy'];
        $pltype=$dbarr['typename'];
        $plsize=$dbarr['size'];
        $pop=$dbarr['maxpopulation'];
        $pophint=getformatednumber($pop);
        $popstr=bd_nice_number($pop);
        $gold=getformatednumber($dbarr['gold']);
        $metalum=getformatednumber($dbarr['metalum']);
        $tritium=getformatednumber($dbarr['tritium']);
        
        
        
        $plninfo="
		  <div class='planetinfo'>
		   <div class='plname'>
		     <b>$plname</b> [ $coords ]		   
		   </div>
   		   <div class='plsize'>
		     <b>$pltype($plsize)</b> 		   
		   </div>
		   <div class='username'>
		     <b>$uname($uid)</b> 		   
		   </div>


		
			<table class='tbplinfo' width='400' border='0' cellspacing='1' cellpadding='0'>
			  <tr>
 			    <th scope='col'>&nbsp;</th>
			    <th scope='col'>Population</th>
			    <th scope='col'>Gold</th>
			    <th scope='col'>Metalum</th>
			    <th scope='col'>Tritium</th>
			  </tr>
			  <tr>
			    <td><b>Storage</b></td>
			    <td title='$pophint'>$popstr</td>
			    <td>$gold</td>
			    <td>$metalum</td>
			    <td>$tritium</td>
			  </tr>
			  <tr>
			    <td><b>Production</b></td>
				<td>&nbsp;</td>
			    <td>$gp</td>
			    <td>$mp</td>
			    <td>$tp</td>
			  </tr>
			  <tr>
			    <td><b>Pr. Bonus</b></td>
			    <td>&nbsp;</td>
			    <td>+$r1</td>
			    <td>+$r2</td>
			    <td>+$r3</td>
			  </tr>
			</table>		
		
		";

        $plninfo.="
			</div>
		";
        $plnstyle="
		  .planetinfo
		  {
		  	left:10px;
			top:635px;
			width:500px;
			height:128px;
			position: absolute;
      		display: block;	
  	  		background-color:#b0c4de;
		  	overflow:auto; 	  
      		z-index: 1;   
			
		  
		  }
		  .plname
		  {
			 left:5px;
			 position:absolute;
			 top:2px;
			 width: 180px;
	  		 font-family:arial;color:blue;font-size:16px;	
   		     text-align:left;
  
		  }
		  .plsize
		  {
			 top:2px;
			 position:absolute; 
			 left:150px;
			 width:100px;
	  		 font-family:arial;color:green;font-size:16px;	
 		     text-align:center;
		  }		  
		  .username
		  {
			 top:2px;
			 position:absolute; 
			 left:400px;
			 width:80px;
	  		 font-family:arial;color:red;font-size:16px;	
 		     text-align:right;
		  }
		  .tbplinfo
		  {
			top:30px;
			position:absolute;  
		  }
		  
		  table, td, th
		  {
			border:1px solid green;
		  }
		  th
		  {
			background-color:green;
			color:white;
		  }
		  tr
		  {
			color:black;
		  }
		  
         table.tbplinfo {table-layout:fixed; width:495px;left:2px;}/*Setting the table width is important!*/		  
         table.tbplinfo td {overflow:hidden;}/*Hide text outside the cell.*/
         table.tbplinfo td:nth-of-type(1) {width:100px;text-align:left;}/*Setting the width of column 1.*/
         table.tbplinfo td:nth-of-type(2) {width:100px;text-align:center;}/*Setting the width of column 2.*/
         table.tbplinfo td:nth-of-type(3) {width:95px;text-align:right;}/*Setting the width of column 3.*/
         table.tbplinfo td:nth-of-type(4) {width:95px;text-align:right;}/*Setting the width of column 4.*/
         table.tbplinfo td:nth-of-type(5) {width:95px;text-align:right;}/*Setting the width of column 5.*/		 		 
		";
        
        addoutput($plninfo, $plnstyle);
    }
    
    function init_page()
    {
        global $ssx,$ssy,$dbmaparr,$selplanet,$mapoffsety,$mapoffsetx;
        global $planetstr,$planetstyl,$solarr,$solx,$soly,$solsyssize;
        global $plg,$plm,$plt;


    
        getsessionvars();
    
    
        $planetstr='';
        $planetstyl='';
        if (myisset(filter_input(INPUT_GET, 'selplanet'))) {
            $selplanet=filter_input(INPUT_GET, 'selplanet');
            $_SESSION['selplanet']=$selplanet;
        } elseif (myisset($_SESSION['selplanet'])) {
            $selplanet=$_SESSION['selplanet'];
        } else {
            adddebugval("Ssx", "$ssx");
            adddebugval("Ssy", "$ssy");
            $selplanet=getplanetfromsolcoords($ssx, $ssy);
        }


        if (myisset($selplanet)) {
            doendturn($selplanet);
            if (myisset(filter_input(INPUT_POST, 'submit'))) {
                if (filter_input(INPUT_POST, 'submit')=='submit') {
                    addnewprodforplanet($selplanet);
                } elseif (filter_input(INPUT_POST, 'submit')=='submit2') {
                    //ship build
                    $stid=filter_input(INPUT_POST, 'shipid');
                    $quant=filter_input(INPUT_POST, 'quant');
                    startbuildingship($selplanet, $plg, $plm, $plt, $stid, $quant);
                    calculateproduction($selplanet, $plg, $plm, $plt);
                } elseif (filter_input(INPUT_POST, 'submit')=='submit4') {
                    //Create fleet
                    postdoplanetfleet($selplanet);
                }
            }
        
      
      
      
            if (myisset(filter_input(INPUT_GET, 'action'))) {
                $act=filter_input(INPUT_GET, 'action');
                switch ($act) {
         case "upgrade":
           $compid=filter_input(INPUT_GET, 'bid');
           upgradebuilding($selplanet, $compid);
         break;
         }
            }
        } else {
            doendturn();
        }
      
        showsolarsystem();
        showgrid();

        if (myisset($selplanet)) {
            showplanetinfo();
            showplanettabs($planetstr, $planetstyl);
            addbuildingstoplanet($selplanet);
        }

        addoutput($planetstr, $planetstyl);
    }
?>