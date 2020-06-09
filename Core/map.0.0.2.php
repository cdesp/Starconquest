<?php

   include_once "mytabset.php";
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once("gamesession.php");
   //map
   include_once("mapdesign.php");
   //tabs
   include_once("mapplanetlist.php");
   include_once("mapfleetlist.php");
   include_once("maproutelist.php");
   
   


   

    
    
    
        //this is called by client through ajax return the tab info
    function mapinforequested($info)
    {
        db_connect();
        activityoccur();

        $ajaxcode='';
        switch ($info) {
        case 0://main map area
            $retarr['content']=getmap(true);
        
        break;
            
        case 1:
            getplanetlist($tabcontent, $tabcontentstyle);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 2:
            getfleetlist($tabcontent, $tabcontentstyle, $ajaxcode, $_SESSION['id']);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
        case 3:
            getroutelist($tabcontent, $tabcontentstyle);
            $sntback= "<style type='text/css'>".$tabcontentstyle."</style>".$tabcontent;
            $retarr['content']=$sntback;
        
        break;
            
        }
        $retarr['scriptcode']=$ajaxcode;
        
        echo json_encode($retarr);
    }

    function mapmain()
    {
        global $selplanet;
        
        $tabarr[0]='PLANETS';
        $tabarr[1]='FLEETS';
        $tabarr[2]='ROUTES';
        
        $maintab="maptabmain";
        
        
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
		
			
		
		   function tabpressed2(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			   ".'
			   varsend="info=1";
			   '."
			 break;
			 case 1:
			   ".'
			   varsend="info=2";
			   '."
			 break;
			 case 2:
			   ".'
			   varsend="info=3";
			   '."
			 break;
			}
			".'
			   varsend="pg=map&"+varsend;			   
			   remurl="Core/getajaxpage.php";
			'."
			   data=getAjaxData(remurl,varsend);
			   var obj = jQuery.parseJSON(data);
			   
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
			 
			   obj=getAjaxInfo(info,'map',varsend,'myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	

			 
		   }
		   
		   function tabpressed(cobj,id)
		   {
			   
			  
			switch (id)
			{
			 case 0:
			    info=1;
			 break;
			 case 1:
			    info=2;
			 break;
			 case 2:
			    info=3;
			 break;
			 
			}
			
				obj=getAjaxInfo(info,'map','p=0','myscript');
			   divobj=getElementByName('$maintab'); 
			   divobj.innerHTML=obj.content;	
				

				 
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
        if (myisset(filter_input(INPUT_GET, 'planinfo'))) {
            $_SESSION['planinfo']=filter_input(INPUT_GET, 'planinfo');
        }
    }
    
    function init_page()
    {
        checkparams();
        
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
            
        getmap();

        $cont='</div>';
        addoutput($cont, '');

        mapmain();
        //fleetdestreached(6); //for testing battles
        //battlefinished(1,true);
    }
    
    if (myisset(filter_input(INPUT_GET, 'info'))) {
        mapinforequested(filter_input(INPUT_GET, 'info'));
    }
