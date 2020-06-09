<?php

   include_once "mytabset.php";
   include_once("galaxyutils.php");
   include_once("myutils.php");
   include_once("gamesession.php");

   includecorepage("techutils");
   includecorepage("techdesign");

        //this is called by client through ajax return the tab info
    function techinforequested($info)
    {
        db_connect();
        activityoccur();
        $selplanet=$_SESSION['selplanet'];
        $playerid=$_SESSION['id'];
        $ajaxcode='';
        $retarr['content']='';
        
        switch ($info) {
        case 0://main map area
            //$retarr['content']=getplanetsurface($selplanet);
        
        break;
            
        case 1:
             $ajaxcode=gettechdataforcat(1);
        break;
        case 2:
             $ajaxcode=gettechdataforcat(2);
        break;
        case 3:
             $ajaxcode=gettechdataforcat(3);
        break;
        case 4:
        
        break;
        case 30:
         //   $ajaxcode=filltechtreedata();
        break;
        
        
            
        }
        $retarr['scriptcode']=$ajaxcode;
        getdebugdata($retarr);
        
        echo json_encode($retarr);
    }



    function techmain()
    {
        global $selplanet,$qrcnt,$tabarr;
        
        $qrres=gettechcat($qrcnt);
        
        adddebugval('catno', $qrcnt);
        for ($i=0;$i<$qrcnt;$i++) {
            $dbarr=query_fetch_array($qrres);
            $tabarr[$i]=$dbarr['catname'];
            adddebugval('cat', $dbarr['catname']);
        }
        
        
        $maintab="techtabmain";
        
        
        $tabcontent="<div id='$maintab' class='$maintab' name='$maintab'> </div>";
        $tabcontentstyle="
		 .$maintab
		 {
			 position:relative;
			left:5;
			top:5;
		  	width:990;
		  	height:530;
		  	overflow:auto;
		  	
			 
		 }
		";
        
        createtab2($maintab, $tabarr, 0, 135, 1000, 600, $tabcontent, 'tabpressed');

        addoutput("", $tabcontentstyle);
        
        //tab pressed so get data through ajax
        $jscript="
		    var mainpage='tech';
		
		   function tabpressed(cobj,id)
		   {
			   
			  
			   info=id+1;
			console.log(info);
			   obj=getAjaxInfo(info,'tech','p=0','myscript');
			   if (obj.content!=''){
			     divobj=getElementByName('$maintab'); 
			     divobj.innerHTML=obj.content;	
			   }

				 
			 // call a default function if exists
				if (typeof initform == 'function')  
				  initform(); 
				if (typeof initform2 == 'function') 
				  initform2(); 
			 
		   }

		
		";
        
        addjscript($jscript);
        
        $incjsf=getjsincludefile('jscript/common.js');
        addincludefile($incjsf);
    }




    //execute to create the page
    function init_page()
    {
        getsessionvars();
       
    
      
        //map
        $cont="<div class='divtecharea' id='divtecharea'>";
        $contstyle="
	      .divtecharea
		  {
			position:absolute;
			left:0;
			top:128;
			width:1024;
			height:635;
			
		  }
	   ";
        techmain();
        addoutput($cont, $contstyle);
            

        $cont='</div>';
        addoutput($cont, '');
        showtechtree(false);
    }
    
    
    if (myisset(filter_input(INPUT_GET, 'info'))) {
        $_SESSION['isajax']=true;
        techinforequested(filter_input(INPUT_GET, 'info'));
        adddebugval('INFO', filter_input(INPUT_GET, 'info'));
        $_SESSION['isajax']=false;
    } else {
        $_SESSION['isajax']=false;
    }




?>

