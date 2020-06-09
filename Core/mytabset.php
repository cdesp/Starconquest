<?php


        //Fix tabs at 110 pixels width
    function createtab2($tabname, $tabarr, $tx, $ty, $tbwidth, $tbheight, $divcontent, $callbackfunc)
    {
        global $pg;
        
        
        if (myisset(filter_input(INPUT_GET, 'tab'))) {
            $tbsel=filter_input(INPUT_GET, 'tab');
        } else {
            if (myisset($_SESSION[$tabname])) {
                $tbsel=$_SESSION[$tabname];
            } else {
                $tbsel=0;
            }
        }
        $_SESSION[$tabname]=$tbsel;
        //		adddebugval('tabnm',$tabname);
//     	adddebugval('tabsel',$tbsel);
        
        $tabno=count($tabarr);
        $t1=$tabno * 110;
        $t2=$tbwidth-$t1;
        $t3=floor($t2 / 55);//count how many tabs we can have
        $tt=$t1+$t3*55; //at least this size to fit the tabs
        $tsize=max($tt, $tbwidth) ;
        //		adddebugval("t1","$t1");
        //		adddebugval("t2","$t2");
        //		adddebugval("t3","$t3");
        //		adddebugval("tt","$tt");
        //		adddebugval("tsize","$tsize");
        $tmpstr="<DIV class='tabset1'>";
        $tmpstl=".tabset1  { 
                        display: block;		   
	 		position: absolute;
	 		z-index: 1;
			left: ".$tx."px;
			top: ".$ty."px;
                        height: 35px; 
                        width: ".$tsize."px; 		
		}
		";
        for ($i=0;$i<$tabno;$i++) {
            $lft=$i*110;
            $tp=0;
            if ($i==$tbsel) {
                $img='tabsl';
                $col='brown';
            } else {
                $img='tabns';
                $col='blue';
            }
            $tmpstr=$tmpstr.'	
	     	
			<div id="tab_'.$i.'" class="tab_'.$i.' mytab" title="'.$tabarr[$i].'" data-id='.$i.' data-cnt='.$tabno.'>
			<div style="font-family:arial;font-size:20px;text-align:center;font-weight:bold;color:'.$col.';margin-left:05px;margin-top:10px">'.$tabarr[$i].'
			</div>
			</div> 
			
		';
        
            $tmpstl=$tmpstl.".tab_$i  { 
                        display: block;
                        background-image: url('Images/$img.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: 55px; 
                        width: 110px; 	
			cursor: pointer;			
		}
		  ";
        }
        $tp=36;
        for ($i=0;$i<$t3;$i++) {
            $lft=$t1+$i*55;
            $tmpstr=$tmpstr.'	
	     	<div id="tabn_'.$i.'" class="tabn_'.$i.'">
			</div>
		';
        
            $tmpstl=$tmpstl.".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: 20px; 
                        width: 55px; 				
		}
		  ";
        }
        $lft=$t1+$i*55;
        $lftover=$tsize-$lft;
        if ($lftover>0) {
            $tmpstr=$tmpstr.'	
	     	<div id="tabn_'.$i.'" class="tabn_'.$i.'">
			</div>
		';
        
            $tmpstl=$tmpstl.".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
            		height: 20px; 
                	width: ".$lftover."px; 							
		}
		  ";
        }
        
        
        
        $lft=0;
        $tp=53;
        $theight=$tbheight-$tp;
        $tmpstr=$tmpstr."<DIV class='tabset_in'>$divcontent</DIV>";
        //$tmpstl.=$contstyle;
        $tmpstl=$tmpstl.".tabset_in  { 
                        display: block;
                        background-image: url('Images/tabbg.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: ".$theight."px; 
                        width: ".$tsize."px; 		
		}
		";
        
        $tmpstr=$tmpstr."</DIV>";
        
        
        //$cont=$tmpstr;
        //$contstyle=$tmpstl;
        addoutput($tmpstr, $tmpstl);
        
        $jscr='		


		
			function settab(tabno)
			{
				//set server session variable			
			   varsend="sesvar='.$tabname.'"+"&'.$tabname.'="+tabno
			   remurl="Core/setsessionvar.php";			
			   data=getAjaxData(remurl,varsend);
			}
		
		'."
			function hittab()
			{
			  $('.tab_$tbsel').trigger('click');
			 
			}

			var selectedTab=-1;
			$(function(){
				  // Functionality starts here
				$('.mytab').on('click', function(e){
					
					tabno=$(this).data('id');
					selectedTab=tabno;
					tabcnt=$(this).data('cnt');
					settab(tabno);
					//change tab image
					for (i=0;i<tabcnt;i++)
					{
					  idnm=	'tab_'+i;
					  divelm=document.getElementById(idnm);				  
					  if (i==tabno) 
					    divelm.style.backgroundImage= ".'"url('."'Images/tabsl.png'".')"'.";						
					  else
                                            divelm.style.backgroundImage= ".'"url('."'Images/tabns.png'".')"'.";						
					}
				
					$callbackfunc(this,tabno);
				    
				})

			});
			
			";
            
        addjscript($jscr);
        addonloadfunction("hittab();");
    }

                
                
        //Variable  size for tabs
    function createtab3($tabname, $tabarr, $tx, $ty, $tbwidth, $tbheight, $divcontent, $callbackfunc, $tbsize=110)
    {
        global $pg;
        
        
        if (myisset(filter_input(INPUT_GET, 'tab'))) {
            $tbsel=filter_input(INPUT_GET, 'tab');
        } else {
            if (myisset(getsessionvar($tabname))) {
                $tbsel=$_SESSION[$tabname];
            } else {
                $tbsel=0;
            }
        }
        $_SESSION[$tabname]=$tbsel;
        //		adddebugval('tabnm',$tabname);
//     	adddebugval('tabsel',$tbsel);
        
        $tabno=count($tabarr);
        $t1=$tabno * $tbsize;
        $t2=$tbwidth-$t1;
        $t3=floor($t2 / 55);//count how many tabs we can have
        $tt=$t1+$t3*55; //at least this size to fit the tabs
        $tsize=max($tt, $tbwidth) ;
        //		adddebugval("t1","$t1");
        //		adddebugval("t2","$t2");
        //		adddebugval("t3","$t3");
        //		adddebugval("tt","$tt");
        //		adddebugval("tsize","$tsize");
        $tmpstr="<DIV class='tabset1'>";
        $tmpstl=".tabset1  { 
                        display: block;		   
	 		position: absolute;
	 		z-index: 1;
			left: ".$tx."px;
			top: ".$ty."px;
                        height: 35px; 
                        width: ".$tsize."px; 		
		}
		";
        for ($i=0;$i<$tabno;$i++) {
            $lft=$i*$tbsize;
            $tp=0;
            if ($i==$tbsel) {
                $img='tabsl';
                $col='brown';
            } else {
                $img='tabns';
                $col='blue';
            }
            $tmpstr=$tmpstr.'	
	     	
			<div id="tab_'.$i.'" class="tab_'.$i.' mytab" title="'.$tabarr[$i].'" data-id='.$i.' data-cnt='.$tabno.'>
			<div id="tab_tit_'.$i.'" style="font-family:arial;font-size:20px;text-align:center;font-weight:bold;color:'.$col.';margin-left:05px;margin-top:10px">'.$tabarr[$i].'
			</div>
			</div> 
			
		';
        
            $tmpstl=$tmpstl.".tab_$i  { 
                        display: block;
                        background-image: url('Images/$img.png') ;
                        background-repeat: no-repeat;
                        background-size: ".$tbsize."px 55px;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: 55px; 
                        width: ".$tbsize."px; 	
			cursor: pointer;			
		}
		  ";
        }
        $tp=36;
        for ($i=0;$i<$t3;$i++) {
            $lft=$t1+$i*55;
            $tmpstr=$tmpstr.'	
	     	<div id="tabn_'.$i.'" class="tabn_'.$i.'">
			</div>
		';
        
            $tmpstl=$tmpstl.".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: 20px; 
                        width: 55px; 				
		}
		  ";
        }
        $lft=$t1+$i*55;
        $lftover=$tsize-$lft;
        if ($lftover>0) {
            $tmpstr=$tmpstr.'	
	     	<div id="tabn_'.$i.'" class="tabn_'.$i.'">
			</div>
		';
        
            $tmpstl=$tmpstl.".tabn_$i  { 
                        display: block;
                        background-image: url('Images/tabno.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
            		height: 20px; 
                	width: ".$lftover."px; 							
		}
		  ";
        }
        
        
        
        $lft=0;
        $tp=53;
        $theight=$tbheight-$tp;
        $tmpstr=$tmpstr."<DIV class='tabset_in'>$divcontent</DIV>";
        //$tmpstl.=$contstyle;
        $tmpstl=$tmpstl.".tabset_in  { 
                        display: block;
                        background-image: url('Images/tabbg.png') ;
	 		position: absolute;
	 		z-index: 1;
			left: ".$lft."px;
			top: ".$tp."px;
                        height: ".$theight."px; 
                        width: ".$tsize."px; 		
		}
		";
        
        $tmpstr=$tmpstr."</DIV>";
        
        
        //$cont=$tmpstr;
        //$contstyle=$tmpstl;
        addoutput($tmpstr, $tmpstl);
        
        $jscr='		
                        var selcolor="aqua";
                        var normcolor="darkblue";

		
			function settab(tabno)
			{
				//set server session variable			
			   varsend="sesvar='.$tabname.'"+"&'.$tabname.'="+tabno
			   remurl="Core/setsessionvar.php";			
			   data=getAjaxData(remurl,varsend);
			}
		
		'."
			function hittab()
			{
			  $('.tab_$tbsel').trigger('click');
			 
			}

			var selectedTab=-1;
			$(function(){
				  // Functionality starts here
				$('.mytab').on('click', function(e){
					
					tabno=$(this).data('id');
					selectedTab=tabno;
					tabcnt=$(this).data('cnt');
					settab(tabno);
					//change tab image
					for (i=0;i<tabcnt;i++)
					{
					  idnm=	'tab_'+i;
                                          txtidnm='tab_tit_'+i;
					  divelm=document.getElementById(idnm);				  
                                          txtelm=document.getElementById(txtidnm);				  
					  if (i==tabno) {
					    divelm.style.backgroundImage= ".'"url('."'Images/tabsl.png'".')"'.";
                                            txtelm.style.color=selcolor;
                                          }
					  else {
                                            divelm.style.backgroundImage= ".'"url('."'Images/tabns.png'".')"'.";						
                                            txtelm.style.color=normcolor;    
                                          }
					}
				
					$callbackfunc(this,tabno);
				    
				})

			});
			
			";
            
        addjscript($jscr);
        addonloadfunction("hittab();");
    }
