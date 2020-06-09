
<?php
   include_once("gamesession.php");
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once "mytabset.php";
   
   
   includecorepage("planetmapdesign");

   includecorepage("planetprod"); //set production
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
            $retarr['content']=getplanetmap(true);
        
        break;
            
        case 1:
            //if (myisset(filter_input(INPUT_GET, 'planinfo'))) $_SESSION['planinfo']=filter_input(INPUT_GET, 'info');
            calculateproduction($selplanet, $plg, $plm, $plt);
            getprodforplanet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= $tabcontent."<style type='text/css'>".$tabcontentstyle."</style>";
            $retarr['content']=$sntback;
        
        break;
        case 2:
            checkshipbuild2();
            getplanetfleet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 3:
            checkshipbuild2();
            getshipsforplanet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 4:
            checkshipbuild2();
            getshipqueueforplanet($tabcontent, $tabcontentstyle, $selplanet, $ajaxcode);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 90://upgrade building
            $bid=filter_input(INPUT_GET, 'bid');
            upgradebuilding($selplanet, $bid);
            
            $retarr['content']="building upgrading bid=$bid";
        
        break;
        
            
        }
        $retarr['scriptcode']=$ajaxcode;
        getdebugdata($retarr);
        
        echo json_encode($retarr);
    }

    
    
    
    function planetmain()
    {
        global $selplanet;
        
        $tabarr[0]='PROD';
        $tabarr[1]='SHIPS';
        $tabarr[2]='BUILD';
        $tabarr[3]='QUEUE';
        
        $maintab="plntabmain";
        
        
        $tabcontent="<div class='$maintab' name='$maintab'> </div>";
        $tabcontentstyle="
		 .$maintab
		 {
			 position:relative;
			left:2;
			top:0;
		  	width:500;
		  	height:565;
		  	overflow:auto;
		  	
			 
		 }
		";
        
        createtab2($maintab, $tabarr, 515, 140, 505, 622, $tabcontent, 'tabpressed');

        addoutput("", $tabcontentstyle);
        
        //tab pressed so get data through ajax
        $jscript="
		
			
		
		   function tabpressed(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			    info=1;
			    remurl='jscript/buildlistvalidate.js';
			   getAjaxScript(remurl);
			 break;
			 case 1:
			    info=2;
			 break;
			 case 2:
			    info=3;
			 break;
			 case 3:
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
        global $selplanet;
        
        if (myisset(filter_input(INPUT_GET, 'selplanet'))) {
            $selplanet=filter_input(INPUT_GET, 'selplanet');
            $_SESSION['selplanet']=$selplanet;
        } elseif (myisset($_SESSION['selplanet'])) {
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
                addnewprodforplanet($selplanet);
            } elseif (filter_input(INPUT_POST, 'submit')=='submit2') {
                //ship build
                $stid=filter_input(INPUT_POST, 'shipid');
                $quant=filter_input(INPUT_POST, 'quant');
                calculateproduction($selplanet, $plg, $plm, $plt);
                startbuildingship($selplanet, $plg, $plm, $plt, $stid, $quant);
                //calculateproduction($selplanet,$plg,$plm,$plt);
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
    }
    
    
    //execute for creating the page
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
        $cont="<div class='divleftarea'>";
        $contstyle="
	      .divleftarea
		  {
			position:absolute;
			left:0;
			top:128;
			width:510;
			height:640;
			
			  
		  }
	   ";
       
        addoutput($cont, $contstyle);
            
        getplanetmap();

        $cont='</div>';
        addoutput($cont, '');
      
      

        if (myisset($selplanet)) {
            //showplanetinfo();
            addbuildingstoplanet($selplanet);
        }

        planetmain();
        //	   addoutput($planetstr,$planetstyl);
    }
    
    
    if (myisset(filter_input(INPUT_GET, 'info'))) {
        $_SESSION['isajax']=true;
        planetinforequested(filter_input(INPUT_GET, 'info'));
        adddebugval('INFO', filter_input(INPUT_GET, 'info'));
        $_SESSION['isajax']=false;
    } else {
        $_SESSION['isajax']=false;
    }

?>