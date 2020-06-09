// JavaScript Document

var curRound = 0;
	curBatid = 0;
	curUserid = 0;
	maxRounds = 0;
	movEnable = 0;
	bgoffsetx = 0;
	bgoffsety = 0;
	quadsizex = 0;
	quadsizey = 0;			
	selfltid = -1;
	bgquadrh = 0;
	bgquadrv = 0;	
	eventsCapt =0;
	myintvar=null;
        selbatid=null;
        battlescriptsloaded=true;
        oldbgcolor=null;
	
/*	
	
$.getScript("jscript/graphics.js", function(data, textStatus, jqxhr){
 // console.log( data ); // Data returned
 //console.log( textStatus ); // Success
 // console.log( jqxhr.status ); // 200
 // console.log( "Load was performed." );	
  battlescriptsloaded=true;
  selid=getAjaxSessionParam('selbatid');
  console.log(selid);
  newbattle(selid);
});	

*/	
function findfleetatxy(qx,qy){
	
	$.each(fleetsarr, function(i, v) {
    	x= fleetsarr [i].scoordx;
	    y= fleetsarr [i].scoordy; 
     	if ((qx==x) && (qy==y)) 
	     return i;	  	   
	});	
	
  return -1;
	
}


	
function doTapeControl(mydiv,ctrl){
	preround=curRound;
	//myimg=mydiv.children[0];
	
  switch (ctrl) {
	case 'start':
				curRound=0;
	  			//myimg.src = "Images/tp_str_en.png";
				//setTimeout(function(){  //Beginning of code that should run AFTER the timeout
				//			myimg.src = "Images/tp_str_ov.png";	
				//			}, 150) 
			break;
	case 'previous':
				if (curRound>0)
  				   curRound--;
	  			//myimg.src = "Images/tp_pre_en.png";
				//setTimeout(function(){  //Beginning of code that should run AFTER the timeout
				//			myimg.src = "Images/tp_pre_ov.png";	
				//			}, 150) 
				
			break;
	case 'next':
				if (curRound<maxRounds)
 				  curRound++;
	  			//myimg.src = "Images/tp_nxt_en.png";
				//setTimeout(function(){  //Beginning of code that should run AFTER the timeout
				//			myimg.src = "Images/tp_nxt_ov.png";	
				//			}, 150) 
				
			break;
	case 'end':
				curRound=maxRounds;	  
	  			//myimg.src = "Images/tp_end_en.png";
				//setTimeout(function(){  //Beginning of code that should run AFTER the timeout
				//			myimg.src = "Images/tp_end_ov.png";	
				//			}, 150) 				
	  		break;
	case 'move':
				if (curRound!=maxRounds) {
					alert("This is an old round! no changes");
					return 0;    
				  }


	  		  if (movEnable==0) movEnable=1; else movEnable=0;  //toggle
			  if (movEnable==1){		
                                  mydiv.style.backgroundImage="url(Images/batmovcmd_en.png)";
				  
				  $('.mainbattlegrid').css( 'cursor', 'crosshair' );
			  }
			  else  {
				  clearCanvas('line');
                                  mydiv.style.backgroundImage="url(Images/batmovcmd.png)";
				  $('.mainbattlegrid').css( 'cursor', 'default' );
			  }
			  
	  		break;
			
	  
  }
  if (preround!=curRound) {
     showround( curRound); 
  }
}

function sethoverimageout(img,imgname){
//	alert(img.id);
	switch (img.id) {
		 case 'imgmovcmd':
			   if (movEnable==1)
				    img.src = imgname+"_en.png";
				  else	
					img.src = imgname+".png";
				break;
		default:
					img.src = imgname+".png";
				break;				
				
	}
}


function getshipselhtml(fltid,stid){
		
	return "<img id='sel' src='Images/selectfleet24.png' width="+(quadsizex+8)+" height="+(quadsizey+8)+" data-fltid="+fltid+"  >" 
   	
}


function gridclicked(x,y){
  var prmxy ={qx:0,qy:0};
	
  if (curRound!=maxRounds) {
	alert("This is an old round! no changes");
	return 0;    
  }

  getquadrfromxy(x,y,prmxy);
  if (prmxy.qx==0)
    return 0;	 

//  alert(prmxy.qx+':'+prmxy.qy);  

	 	
  if (selfltid==-1) {	alert ('Choose a ship to move');return 0;}
  
	 
	 
  shp=findfleetatxy(prmxy.qx, prmxy.qy);
  if (shp!=-1) {
	   if ((fleetsarr[shp].fltid==selfltid) )
	    return 0;
	   else
	    {alert ('There is a fleet there choose another position');return 0;}
  }

 // flt=findfleet(selfltid);//selected fleet
  if (fleetsarr[selfltid].ownerid!=curUserid) 
	    {alert ('This is not your ship');return 0;}


//  alert('Ship '+selfltid+"_"+selstid+" will move to ["+prmxy.qx+':'+prmxy.qy+']');
	//sent action to server
	params="fltid="+selfltid+"&batid="+curBatid+"&x="+prmxy.qx+"&y="+prmxy.qy;
	obj = getAjaxInfo(50,'battle',params); 
//	alert(obj.content);
	fleetsarr[selfltid].cmdmovcx=prmxy.qx;
	fleetsarr[selfltid].cmdmovcy=prmxy.qy;
	//alert(fleetsarr[selfltid].cmdmovcx);
	doShipClicked(selfltid);
}

	



function animate(){
   canv1=document.getElementById('sensor');
   canv2=document.getElementById('sensor2');  	
   if ((canv1!=null) && (canv2!=null)){
	   
	 f=canv1.hidden;
	 canv1.hidden=!f;
	 canv2.hidden=f;   
	   
   }
}

function mycostfunc(n1,n2){
	
	return 1;
}



function showlineto(grd){ //using simple bresenham line algorithm
	
	clearCanvas('line');
	
	fx=start.x+1;fy=start.y+1;
	x=grd[0];y=grd[1];
    var path = get_line(fx,fy,x,y);
  	if(!path || path.length == 0) {console.log('path not found');return 0;}

	rcs=getcoordsfromgrid(fx,fy,true);
//	console.log('line');
	
	for (i=0;i<path.length;i++) {
  	  rce=getcoordsfromgrid(path[i].x,path[i].y,true);	  	
	  paintline(rcs[0],rcs[1],rce[0],rce[1],'line','mainbattlegrid',"white",false,[2,1]);
	  rcs=rce;
	}


	
//  rcs=getcoordsfromgrid(fx,fy,true);
//	rce=getcoordsfromgrid(x,y,true);	
//	paintline(rcs[0],rcs[1],rce[0],rce[1],'line',"white",false)
}


function domousemove(mx,my){
 
  grd=[0,0];
  if (typeof pregrd=='undefined') pregrd=[0,0];
  if (!getgridcoords([mx,my],grd)) return 0;
//  $('#tpinfo').html(mx +', '+ my+"<br>"+grd[0]+','+grd[1]);
  if ((movEnable!=0) && (start!=null)){
		  if ((pregrd[0]==grd[0]) && (pregrd[1]==grd[1])  ){
//			  console.log('equal');
		  } else
		    showlineto(grd);

		  pregrd=grd;
  }
	
}


function getcoordsfromgrid(x,y,center){
  //x++;y++;	
  rc=new Array();
  rc[0]=bgoffsetx+(x-1)*quadsizex;	
  rc[1]=bgoffsety+(y-1)*quadsizey;		
  if (center){
	rc[0]+=quadsizex/2;
	rc[1]+=quadsizey/2; 
  }
  return rc;
}

function paintfleetroute(fltid){
	  if ((fleetsarr[fltid].cmdmovcx!=0) && (fleetsarr[fltid].cmdmovcy!=0)){
	    rcs=new Array();grds=new Array();
		grds[0]=fleetsarr[fltid].scoordx;
		grds[1]=fleetsarr[fltid].scoordy;
	//	console.log(grds[0]+":"+grds[1]);		
	    rce=new Array();grde=new Array();
		grde[0]=fleetsarr[fltid].cmdmovcx;
		grde[1]=fleetsarr[fltid].cmdmovcy; 
	//	console.log(grde[0]+":"+grde[1]);
		rcs=getcoordsfromgrid(grds[0],grds[1],true); 		
		rce=getcoordsfromgrid(grde[0],grde[1],true); 		

		paintline(rcs[0],rcs[1],rce[0],rce[1],'sensor','mainbattlegrid','#8A2BE2',true,[]);
		paintline(rcs[0],rcs[1],rce[0],rce[1],'sensor2','mainbattlegrid','#BF3EFF',true,[]);		
	  }
	
}

function doShipClicked(fltid){
	
	//console.log('ships');
  var divshpsel=document.getElementById('divshpsel');

  if (divshpsel!=null)
    divshpsel.parentNode.removeChild(divshpsel);

//add the div  
	  divparent=getElementByName('mainbattleround');
	  if (divparent==null) {alert('div batleround not found');return false;}
	  var temp = document.createElement('div'); 
      divship=document.getElementById('ships_'+fltid);
	  if (divship==null) return 0;//ship destroyed???
	  mx=parseInt(divship.style.left)-4;
	  my=parseInt(divship.style.top)-4;


	  temp.innerHTML=" <div id='divshpsel' style='position:absolute;z-index=3;left:"+mx+";top:"+my+"'>"+getshipselhtml(fltid,stid)+"	 </div>";
	  var childs = temp.childNodes;
	  divparent.appendChild(childs[1]);	  
	
	  selfltid=fltid;
	  
	 // sens=stypearr[stid].sensdist;
//  	  speed=getShipSpeed(stypearr[stid].speed);
	  speed=getFleetSpeed(fltid);
	  //TODO***
		  weapondist=0;
		   for (i=0;i<shipsarr.length;i++)
		     if (shipsarr[i].fltid==fltid){
				 //totships++;
				 stid=shipsarr[i].stid;
  		        weapondist=Math.max(stypearr[stid].wdist1,stypearr[stid].wdist2,stypearr[stid].wdist3,weapondist);
			 }
			 console.log('weapondist='+weapondist);
	 // alert(speed+" "+sens);
	  centerx=mx+4+quadsizex/2;
	  centery=my+4+quadsizey/2;
	  clearCanvas('sensor');
	  clearCanvas('sensor2');	  
	 
	  paintfleetroute(fltid);	  

	  drawCircle(centerx, centery, speed*quadsizex-2,speed*quadsizey-2,"lime",'sensor','mainbattlegrid');	  
	  drawCircle(centerx, centery, weapondist*quadsizex-4,weapondist*quadsizey-4,"aqua",'sensor','mainbattlegrid');	  	 

	  drawCircle(centerx, centery, speed*quadsizex-2,speed*quadsizey-2,"green",'sensor2','mainbattlegrid');	  
	  drawCircle(centerx, centery, weapondist*quadsizex-4,weapondist*quadsizey-4,"blue",'sensor2','mainbattlegrid');	  	 
	 
	  canv2=document.getElementById('sensor2');
	  if (canv2!=null) canv2.hidden=true;


	  clearInterval(myintvar);
	  myintvar=setInterval(animate,400);
	  msxy=[centerx,centery];grdxy=[0,0];
	  getgridcoords(msxy,grdxy);
	  x=grdxy[0]-1;y=grdxy[1]-1;
	  start ={x:x,y:y};
//	  alert(start);

}

function captureevents(){
	
		$('.tape').on('click', function(e){					
					ctrl=$(this).data('action');
					doTapeControl($(this).get(0),ctrl);						
				});


	
}


function showround(Round){
	movEnable =0;//not enabled the move
	//document.getElementById('imgmovcmd').src="Images/batmovcmd.png";
	$('.mainbattlegrid').css( 'cursor', 'default' );
	
	//get the ships
	
	params="round="+Round+"&batid="+curBatid;
	obj = getAjaxInfo(20,'battle',params,'batroundscr');	//get 	
        divobj=getElementByName('mainbattleround'); 
        if (obj!=null)
	  divobj.innerHTML=obj.content;	
	
	
//	alert(obj.scriptcode);
	
//	alert(shipsarr[0].batid);
        divobj=document.getElementById('atkuser'); 
        if (divobj!=null){
           divobj.innerText=atkuser;
        }
        divobj=document.getElementById('defuser'); 
        if (divobj!=null){
           divobj.innerText=defuser;
        }


        divobj=document.getElementById('tpinfo'); 
	if (divobj!=null)
    	  divobj.innerText="Round "+Round+" of "+maxRounds;
        else
          console.log('tpinfo');   
        
        if (typeof atkreport === 'undefined') {
            return;
        }
        $('.atkreport').html(atkreport); 
        $('.defreport').html(defreport); 

		$('.ships').each(function(i, obj) {
		   fltid=$(this).data('fltid');	

			obj.title='';
			weapondist=0;maxsize=0;
			hullstid=0;totships=0;
                        types='';
                        username=fleetsarr[fltid].username;
                       // console.log(fleetsarr[fltid]);
		   for (i=0;i<shipsarr.length;i++)
		     if (shipsarr[i].fltid==fltid){
				 //totships++;
				stid=shipsarr[i].stid; 
                                totships=shipsarr[i].quantity;
                               // console.log(shipsarr[i]);
                               // console.log(fleetsarr[fltid]);
                                
                                killed=shipsarr[i].killed;
                                weapondist=Math.max(stypearr[stid].wdist1,stypearr[stid].wdist2,stypearr[stid].wdist3,weapondist);
				maxsize=Math.max(maxsize,stypearr[stid].maxsize);                                
				if (stypearr[stid].maxsize==maxsize) hullstid=stid;//image of the biggest
                                shipsleft=totships-killed;
                                types+="("+shipsleft+") "+ stypearr[stid].stypename+" (-"+killed+")\n";
			 }
               obj.title=username+"\n-------------------\n"+types+"\nSpeed : "+getFleetSpeed(fltid)+" (Green)\n"+"Weapon : "+weapondist+" (Blue)\n\n";

			   
		});

		$('.ships').on('click', function(e){					
					fltid=$(this).data('fltid');
					//stid=$(this).data('stid');					
					stid=0;
					doShipClicked(fltid);						
				});

		$('.mainbattlegrid').unbind('click');
		$('.mainbattlegrid').click(function(e) {
    		var offset = $(this).offset(); 
			if (movEnable!=0)
  			  gridclicked(e.clientX - offset.left,e.clientY - offset.top);
	    });
		$('.mainbattlegrid').mousemove(function(event){
			var parentOffset = $(this).parent().offset(); 

			var relx=event.pageX-parentOffset.left;
			var rely=event.pageY-parentOffset.top;
			domousemove(relx, rely);

		});

	clearCanvas('sensor');
	clearCanvas('sensor2');
	if (selfltid!=-1) doShipClicked(selfltid);
}


function newbattle(batid){
//  	alert('new battle '+batid);	
  if (!battlescriptsloaded) return;  
  page='battle';
    divobj=getElementByName('divmesg'); 
	divobj.innerHTML="";	

	curBatid = batid;
	
	
	if (curUserid==0)
  	  curUserid=getAjaxSessionParam('id');
	myelm=document.getElementById('batdiv_'+batid);
	//selbatid is defined in php battle.php
	if ((selbatid!=null) && (selbatid!=batid)) {
		oldelm=document.getElementById('batdiv_'+selbatid);                
		//alert(selbatid);
                if (oldelm!=null)
		  oldelm.style.backgroundColor=oldbgcolor;                
	}
        if (myelm!=null){
          if (oldbgcolor==null) oldbgcolor=myelm.style.backgroundColor;
	  myelm.style.backgroundColor="#113264";
        }
	
	selbatid=batid; 
        setAjaxSessionParam('selbatid',selbatid);
	obj = getAjaxInfo(0,page);
	
        divobj=getElementByName('mainbattlegrid');
        if (obj!=null)
	 divobj.innerHTML=obj.content;	
	
	//get the battle controls
	params="round="+curRound+"&batid="+curBatid;
	obj = getAjaxInfo(21,page,params);	//get 
        divobj=getElementByName('battlecontrols'); 
        if (obj!=null)
  	  divobj.innerHTML=obj.content;	
        if (obj!=null)
	 maxRounds=obj.maxround;	
	curRound = maxRounds;//0=setup round
        
        //set final battle report
	params="batid="+curBatid;
	obj = getAjaxInfo(22,page,params);	//get 
        divobj=getElementByName('fullreport'); 
        if (obj!=null)
	  divobj.innerHTML=obj.content;	
    
    
    
	captureevents();	
	bgoffsetx=getAjaxParam(page,'bgoffsetx');
	bgoffsety=getAjaxParam(page,'bgoffsety');	
	quadsizex=getAjaxParam(page,'quadsizex');
	quadsizey=getAjaxParam(page,'quadsizey');	
	bgquadrh=getAjaxParam(page,'bgquadrh');
	bgquadrv=getAjaxParam(page,'bgquadrv');	

	//first time we should set the size of canvas
	loadCanvas('mainbattlegrid','sensor',bgoffsetx+bgquadrh*quadsizex,bgoffsety+bgquadrv*quadsizey);
	loadCanvas('mainbattlegrid','sensor2',bgoffsetx+bgquadrh*quadsizex,bgoffsety+bgquadrv*quadsizey);
	loadCanvas('mainbattlegrid','line',bgoffsetx+bgquadrh*quadsizex,bgoffsety+bgquadrv*quadsizey);
	
	selfltid=-1;
	showround(curRound);
	
}


//returns grid coords from real coords
function getgridcoords(msxy,grdxy){
	grdxy[0]=Math.floor((msxy[0]-bgoffsetx)/quadsizex)+1;
	grdxy[1]=Math.floor((msxy[1]-bgoffsety)/quadsizey)+1;
	if (grdxy[0]<1 || grdxy[0]>bgquadrh || grdxy[1]<1 || grdxy[1]>bgquadrv)
	  return false; else return true;
}


function getquadrfromxy(x,y,prmxy){
  prmxy.qx=Math.floor((-bgoffsetx+x)/quadsizex)+1;
  prmxy.qy=Math.floor((-bgoffsety+y)/quadsizey)+1;	
  if ((prmxy.qx<1) || (prmxy.qx>bgquadrh) || (prmxy.qy<1) || (prmxy.qy>bgquadrv)){ prmxy.qx=0;prmxy.qy=0;}
 
  //alert(qx+':'+qy);
}


function getShipSpeed(sp){
  return Math.floor(sp/10);
}

function getFleetSpeed(fltid){
	sp=9999;
	for (i=0;i<shipsarr.length;i++)
	 if (fltid==shipsarr[i].fltid){
		 stid=shipsarr[i].stid;
	     sp=Math.min(sp,stypearr[stid].speed); 	  	 
	 }
	
	
  return Math.floor(sp/10);
}

function findfleet(fltid){
	
	$.each(fleetsarr, function(i, v) {
 	  if ((fltid==fleetsarr [i].fltid)) 
	    return i;	  
	});	

  return -1;	
}


