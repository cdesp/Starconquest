
<?php
   include_once("gamesession.php");
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once "mytabset.php";
   
   
   //includecorepage("planetmapdesign");
   includecorepage("planetsurface");

   //includecorepage("planetprod"); //set production
   includecorepage("planetfleet"); //show planet ships
   includecorepage("planetship"); //set ships to build
   includecorepage("planetqueue"); //show build queue
 //  include_once ("shipbuild.php");
   
   include_once("srvendturn.php");
   




    
    
        //this is called by client through ajax return the tab info
    function planetinforequested($info)
    {
        db_connect();
        activityoccur();
        $selplanet=$_SESSION['selplanet'];
        $ajaxcode='';
        
        switch ($info) {
        case 0://main map area
            $retarr['content']=getplanetsurface($selplanet);
        
        break;
            
        case 1://Planet production list requested *** NOT USED ANYMORE ***
            //if (myisset(filter_input(INPUT_GET, 'planinfo'))) $_SESSION['planinfo']=filter_input(INPUT_GET, 'info');
        /*	calculateproduction($selplanet,$plg,$plm,$plt);
            getprodforplanet($tabcontent,$tabcontentstyle,$selplanet,$ajaxcode);
            $sntback= $tabcontent."<style type='text/css'>".$tabcontentstyle."</style>";
            $retarr['content']=$sntback;*/
        
        break;
        case 2://Planet ship list requested
            checkshipbuild3($selplanet);
            getplanetfleet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 3://Planet Select Build Ship Type list
            checkshipbuild3($selplanet);
                        getshipsforplanet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 4:
            checkshipbuild3($selplanet);
            getshipqueueforplanet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 30:
            calculateproduction($selplanet, $plg, $plm, $plt);
            checkshipbuild3();
            $ajaxcode=fillplanetdata($selplanet);
        break;
        
        case 90://upgrade building
            $bid=filter_input(INPUT_GET, 'bid');
            upgradebuilding($selplanet, $bid);
            $retarr['content']="building upgrading bid=$bid";
        break;
        case 91://upgrade building
            $bid=filter_input(INPUT_GET, 'bid');
            $retarr['content']=upgradebuilding2($selplanet, $bid);
        break;
        
            
        }
        $retarr['scriptcode']=$ajaxcode;
        getdebugdata($retarr);
        
        echo json_encode($retarr);
    }

    
    
    
    function planetmain()
    {
        global $selplanet;
        
        //$tabarr[0]='PROD';
        $tabarr[0]='SHIPS';
        $tabarr[1]='BUILD';
        $tabarr[2]='QUEUE';
        
        $maintab="plntabmain";
        
        
        $tabcontent="<div class='$maintab defmaintab' name='$maintab'> </div>";
        $tabcontentstyle="
		 .$maintab
		 {
                    width:417;
		 }
		";
        
        //		createtab2($maintab,$tabarr,515,140,505,622,$tabcontent,'tabpressed');
        $tp=130;
        $hgt=1024-$tp-100-150;//150 is top menu //100 is tab top
        createtab3($maintab, $tabarr, 600, $tp, 420, $hgt, $tabcontent, 'tabpressed', 120);

        addoutput("", $tabcontentstyle);
        
        //tab pressed so get data through ajax
        $jscript="
		
			
		
		   function tabpressed(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			    info=2;
			 break;
			 case 1:
			    info=3;
			 break;
			 case 2:
			    info=4;
			 break;
			 
			}

			   obj=getAjaxInfo(info,'planet','p=0','myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
			  		  
			   if (obj.scriptcode!='')
  			     addScriptToPage(obj.scriptcode,'myscript');
			   

			 // call a default function if exists
				if (typeof initform == 'function') { 
				  initform(); 
				}//	else alert('no func');		 
		   }
		
		";
        
        addjscript($jscript);
        
        $incjsf=getjsincludefile('jscript/common.js');
        addincludefile($incjsf);
    }

    


    
    
    function checkparams()
    {
        if (myisset(filter_input(INPUT_GET, 'selplanet'))) {
            $selplanet=filter_input(INPUT_GET, 'selplanet');
            $_SESSION['selplanet']=$selplanet;
        } elseif (myisset(getsessionvar('selplanet'))) {
            $selplanet=$_SESSION['selplanet'];
        } else {
            adddebugval("Ssx", "$ssx");
            adddebugval("Ssy", "$ssy");
            $selplanet=getplanetfromsolcoords($ssx, $ssy);
            $_SESSION['selplanet']=$selplanet;
        }
           
        
        if (myisset(filter_input(INPUT_GET, 'open'))) {
            $open=filter_input(INPUT_GET, 'open');
        } else {
            $open=3;
        }
            
        $_SESSION['open']=$open;
           
        return $selplanet;
    }
    
    function checkbuttons()
    {
        $selplanet=$_SESSION['selplanet'];
        
        if (myisset(filter_input(INPUT_POST, 'submit'))) {
            if (filter_input(INPUT_POST, 'submit')=='submit') {
                adddebug("add new prod planet .");
                addnewprodforplanet($selplanet);
            } elseif (filter_input(INPUT_POST, 'submit')=='submit2') {
                //ship build
                adddebug("ship build .");
                $stid=filter_input(INPUT_POST, 'shipid');
                $quant=filter_input(INPUT_POST, 'quant');
                calculateproduction($selplanet, $plg, $plm, $plt);
                startbuildingship($selplanet, $plg, $plm, $plt, $stid, $quant);
                //calculateproduction($selplanet,$plg,$plm,$plt);
            } elseif (filter_input(INPUT_POST, 'submit')=='submit4') {
                //Create fleet
                adddebug("Create fleet .");
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
    }
    
    
    //execute to create the page not executed from ajax
    function init_page()
    {
        getsessionvars();
        $selplanet=checkparams();
    
        if (myisset($selplanet)) {
            doendturn($selplanet);
            checkbuttons();
        } else {
            doendturn();
        }
      
        //map
        $cont="<div class='divleftarea dlaplanet' id='divleftarea'>";
        $contstyle="
	      
	   ";
       
        addoutput($cont, $contstyle);
            
        //	getplanetmap();
        showplanetsurface();

        $cont='</div>';
        addoutput($cont, '');
      
      
     
        if (myisset($selplanet)) {
            adddebugval('SelPlanet', $selplanet);
            //showplanetinfo();
            addbuildingstoplanet($selplanet);
        }

        planetmain();
        //	   addoutput($planetstr,$planetstyl);
    }
    

        
// Always run when included
        
    if (myisset(filter_input(INPUT_GET, 'info'))) {
        //		$_SESSION['isajax']=true;
        planetinforequested(filter_input(INPUT_GET, 'info'));
        adddebugval('INFO', filter_input(INPUT_GET, 'info'));
        //		$_SESSION['isajax']=false;
    } else {
        $_SESSION['isajax']=false;
    }

?>