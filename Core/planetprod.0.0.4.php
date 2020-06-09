<?php

include_once("gamesession.php");
include_once("planetutils.php");

//Shows the planet list owned by player on a tab

function getprodforplanet(&$tabcontent, &$tabcontentstyle, $pid, &$ajaxcode, $par = null, &$jschild = null)
{
    $userid = $_SESSION['id'];
    getplanetowner($pid, $pluserid, $plusername);
    if ($pluserid != $userid) {
        $tabcontent = "You dont own that planet $pluserid != $userid";
        return false;
    }

    $tabselected = 0;

    $open = $_SESSION['open'];
    $isajax = $_SESSION['isajax'];


    if (!myisset($par)) {
        $parst = "`bparentid` is NULL";
        $tabcontent = '<div class="planprod">';
        $ts = 'planet';
        $pr = 0;
        $jsroot = '';
        $jschild = '';
    } else {
        $parst = "`bparentid`=$par";
        $ts = 'industry';
        $pr = $par;
    }
    $ts .= " prod.";
    $curvars = $_SERVER["REQUEST_URI"];
    $quer = "SELECT * FROM `buildings`,`buildingtypes`,`buildinglevels` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and `buildings`.`btid`=`buildinglevels`.btid and `level`=`blevel`+1 and $parst order by btype";

    if (executequery($quer, $qres, $qrcnt)and $qrcnt > 0) {
        adddebug("Found $qrcnt Buildings for planet $pid <br>");
        if (!myisset($par)) {
            $tabcontent .= "
					<form name='builtlist' method='post' onsubmit='return validateForm()' >
					";
        }

        for ($i = 1; $i < $qrcnt + 1; $i++) {
            $dbarr = query_fetch_array($qres);
            $bname = $dbarr['bname'];
            $bperc = $dbarr['percnt'];
            $blvl = $dbarr['blevel'];
            $gold = $dbarr['gold'];
            $metal = $dbarr['metalum'];
            $tritium = $dbarr['tritium'];
            $goldupg = $dbarr['goldupg'];
            $metalupg = $dbarr['metalumupg'];
            $tritiumupg = $dbarr['tritiumupg'];
            $upgrading = $dbarr['baction'] == 1;
            $upgdays = $dbarr['daysupg'];
            $upghours = $dbarr['hoursupg'];
            $upgmins = $dbarr['minsupg'];
            $upglevel = $dbarr['blevel'] + 1;
            $tmdur = maketimetoupg($upgdays, $upghours, $upgmins);
            $tmdurs = gettimetoupgrade($tmdur);

            if ($upgrading) {
                $ttt = calctimetoupgrade($dbarr['bacttimestart'], $dbarr['bacttimedur']);
                $timetoupg = gettimetoupgrade($ttt);
                $upgs = '▲U... ' . $timetoupg;

                adddebug($timetoupg . '<br>');
                //todo : get this out of here
                if (istimetoupg($dbarr['bacttimestart'], $dbarr['bacttimedur'])) {
                    //levelup
                    upgradeplanetbuilding($pid, $dbarr['btid']);
                }
            } else {
                $upgs = '';
            }

            //getupgresneeded($goldupg,$metalupg,$tritiumupg,$blvl+1);

            if (!myisset($par)) {
                $tabcontent = $tabcontent . "<b>";
                $jsroot = $jsroot . 'rootcomps[' . $i . ']="buildrng_' . $pr . $i . '";';
            } else {
                $jschild = $jschild . 'childcomps[' . $i . ']="buildrng_' . $pr . $i . '";';
            }
            $tabcontent = $tabcontent . " 
			  <div class='builddiv_$pr$i'><div  style='position:absolute;left:00px'>
				$i.";
            if (!myisset($par) and $open != $i) {
                $tabcontent = $tabcontent . "	
				<a href='?pg=planet&tab=$tabselected&open=$i'>	";
            }
            $tabcontent = $tabcontent . $bname;

            if (!myisset($par) and $open != $i) {
                $tabcontent = $tabcontent . "</a>";
            }

            $hint = "NEED FOR UPGRADE&#013 Resources:  g:$goldupg m:$metalupg t:$tritiumupg&#013";
            $tabcontent = $tabcontent . " ($blvl) $upgs</div>   
				
				<div style='position:absolute;left:350px;color:green'>STORAGE</div>

				<div style='position:absolute;top:20px;left:310px'>g:$gold m:$metal t:$tritium  </div>
				<div style='position:absolute;top:18px;'>
				";
            if (!$upgrading) {
                $tabcontent = $tabcontent . "
				<a href='javascript:upgBuilding(" . '"' . "$pr$i" . '"' . ")'> <img src='Images/upgbuild.png' title='$hint Time: $tmdurs'/> </a>";
            } else {
                $tabcontent = $tabcontent . "
				 <img src='Images/upgbuild.png' title='Upgrading to level $upglevel ($tmdurs)'/>";
            }
            $tabcontent = $tabcontent . "
					</div>
					<input type='range' class='buildrng' name='buildrng_$pr$i' id='buildrng_$pr$i' min='0' max='100' value='$bperc' step='5' list='number' onchange='" . 'onChangeVal("' . $pr . $i . '")' . "' /> 
				<datalist id='number'>
 					 <option>0</option> 
 					 <option>10</option> 
					 <option>20</option> 
					 <option>30</option> 
					 <option>40</option> 
					 <option>50</option> 
 					 <option>60</option> 
					 <option>70</option> 
					 <option>80</option> 
					 <option>90</option> 
					 <option>100</option> 					 
				</datalist>
				<div  style='position:relative;left:170;top:-12;width:120'><output id='rangevalue_$pr$i' style='color:green' >$bperc</output> % of $ts </div>
			
				
			
			";
            if (!myisset($par)) {
                $tabcontent = $tabcontent . "</b>";
            }
            if ($open == $i) {
                getprodforplanet($tabcontent, $tabcontentstyle, $pid, $ajaxcode, $dbarr['btid'], $jschild);
            }
            $tabcontent = $tabcontent . "</div>";
            if (!myisset($par)) {
                $tabcontent = $tabcontent . "<br><hr>";
            }


            if (!myisset($par)) {
                $margin = "10px";
            } else {
                $margin = "20px";
            }
            $tabcontentstyle .= "
				.builddiv_$pr$i
				{
 				  font-family:arial;color:#8B3626;font-size:12px;	 	  
				  position: relative;
			      display: block;      	
				  left:$margin;	
				  width:465px;
				}
			";
        }


        $tabcontentstyle .= "
		
				.buildrng
				{
					##-webkit-appearance: none;  
					position: relative;
    				width: 120px;  
   					height: 30px;  
					left:30;
					top:13px;
    				padding: 3px;  
   					-webkit-border-radius: 15px;  
    				border-radius: 15px;  
    				border: 1px solid #525252;  
    				background-image: -webkit-gradient(  
        				linear,  
						right top,
						left top,
						color-stop(0.45, rgb(204,84,84)),
						color-stop(0.61, rgb(72,181,67))
						);					
				}
		
		";
        if (!myisset($par)) {
            $tabcontent = $tabcontent . "
		 	<div style='position:relative;left:170px;width:200px;'>
		 	  <input type = 'hidden' name = 'submit' value = 'submit'>
				<input type='image' name='submit' value='submit' 
					src='Images/submit_up.png' border='0' 
			 		onmouseover='" . 'this.src="Images/submit_dn.png"' . "'
					onmouseout='" . 'this.src="Images/submit_up.png"' . "'	
				/>
			</div>
		    </form>
			</div>";
        }

        if ($par == null) {
            $tabcontentstyle = $tabcontentstyle . "
			   .planprod
			   {
			  
			   height:569px; 
			   width:505px;
			   }
-webkit-scrollbar-track {
      background-color: #b46868;
} /* the new scrollbar will have a flat appearance with the set background color */
-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.2);
} /* this will style the thumb, ignoring the track */
-webkit-scrollbar-button {
      background-color: #7c2929;
} /* optionally, you can style the top and the bottom buttons (left and right for horizontal bars) */
-webkit-scrollbar-corner {
      background-color: black;
} /* if both the vertical and the horizontal bars appear, then perhaps the right bottom corner also needs to be styled */				  	
			";
        }



        if ($par == null) {
            $jscr = "
					function upgBuilding(bid){
						info=90;
						console.log(bid);
						obj=getAjaxInfo(info,'planet','bid='+bid,'planprodscr');
						//alert(obj.content);
						tabpressed(null,selectedTab);	
					}\n
					
				";

            if (!$isajax) {
                addjsfunction('initform', $jsroot . $jschild);
            } else {
                $ajaxcode .= $jscr . getajaxjsfunction('initform', '//alert("hi");' . $jsroot . $jschild);
            }
        }
    }
}
?>

