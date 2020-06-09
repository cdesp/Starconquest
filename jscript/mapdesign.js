// JavaScript Document

var loaded=true;
/*
$.getScript("jscript/astar/graph.js", function(){
	
	
//  console.log(loaded);	
});	

$.getScript("jscript/graphics.js", function(){

  //console.log(loaded);
});	

$.getScript("jscript/astar/astar.js", function(){  

  loaded=true;
//  console.log(loaded);
});	
*/

var
	moveenabled=false;
	//for star algorithm
	grid = null;
	graph =null;
    start = null;
    end = null;
	pregrd=[0,0];
	
function getRealCoords(objGrid,objReal){
//	console.log('ssx='+ssx);
 nssx=Math.max(0,ssx-1);	
 nssy=Math.max(0,ssy-1);		
 objGrid.x-=nssx*solsyssize;
 objGrid.y-=nssy*solsyssize;
// console.log('objGrid.x='+objGrid.x);
 objReal.x=mapoffsetx+objGrid.x*tilesizex;
 objReal.y=(mapoffsety-128)+objGrid.y*tilesizex;
}

function showSelectedPlanet(){
		   plandiv=document.getElementById('fleetplandiv');
		   quadr=document.getElementById('selected');
//		   if (quadr!=null)
//  		      plandiv.removeChild(quadr);

		   if (selplanet>=0)
 		      value=planetarr[selplanet];
			  else return false;
			
		   if (typeof value=='undefined') return false;
		   
		   objReal={x:-1,y:-1};
		   getRealCoords({x:value['coordx'],y:value['coordy']},objReal);
		   ownername=value['username'];
		   hint = value['name']+"("+value['typename']+")\n["+value['coordx']+":"+value['coordy']+"]\n ("+ownername+")"; 
		   tid='selected';
		   
		   if (quadr==null){
  	          quadr=document.createElement("div");
			  newlycreated=true;
		   }
		   else newlycreated=false;
		   quadr.id=tid;
		   quadr.className=tid;
		   quadr.setAttribute('data-id',value['pid'] );
		   quadr.title=hint;
		   quadr.width=20;
		   quadr.height=20;
		   quadr.style.left=objReal.x-2;
		   quadr.style.top=objReal.y-2;
		   quadr.style.position='absolute';
		   img=document.createElement('img');
	       img.setAttribute('src', 'Images/selectplanet24.png');
           img.setAttribute('height', quadr.width+'px');
           img.setAttribute('width', quadr.height+'px');
		   if (newlycreated){
  		     quadr.appendChild(img);
	         plandiv.appendChild(quadr);	
			 $('.selected').css( 'cursor', 'pointer' );	
		   }
			
	
	
}
	
function showArea(){
   canv=getCanvas('gridcanv');
   	
   mynssx=Math.max(0,ssx-1)*10;	
   mynssy=Math.max(0,ssy-1)*10;	

	
	
   objrl={x:-1,y:-1};
   objgr={x:-1,y:-1};
   
   for (mx=mynssx;mx<mynssx+30;mx++){
 	for (my=mynssy;my<mynssy+30;my++) {
		objgr.x=mx;objgr.y=my;
		getRealCoords(objgr,objrl);
		
	  	//drawCircle(objrl.x+5,objrl.y+5,4,4,44,'gridcanv','');		
	}
   }
	
	
	
}
	
	
function showPlanets(){
		plandiv=document.getElementById('fleetplandiv');

		$.each(planetarr, function(index, value) {
		  // console.log(index+"."+value['coordx']+":"+value['coordy']);
		   objReal={x:-1,y:-1};
		   getRealCoords({x:value['coordx'],y:value['coordy']},objReal);
		   //console.log(index+"."+objReal.x+":"+objReal.y);
		   ownername=value['username'];		   
		   hint = value['name']+"("+value['typename']+")\n["+value['coordx']+":"+value['coordy']+"]\n ("+ownername+")"; 
		   imgname=value['imagename']+'16.png';
		   tid='quadr_'+value['pid'];
		   
	       quadr=document.createElement("div");
		   quadr.id=tid;
		   quadr.className='planets';
		   quadr.setAttribute('data-id',value['pid'] );
   		   quadr.setAttribute('data-idx',index );		   
		   quadr.title=hint;
		   quadr.width=16;
		   quadr.height=16;
		   quadr.style.left=objReal.x;
		   quadr.style.top=objReal.y;
		   quadr.style.position='absolute';
		   img=document.createElement('img');
		   img.setAttribute('src', 'Images/'+imgname);
           img.setAttribute('height', quadr.width+'px');
           img.setAttribute('width', quadr.height+'px');
		   quadr.appendChild(img);
		   if (value['ownerid']>0) {
			   img=document.createElement('img');
			   //console.log(userid);
			   if (value['ownerid']==userid)		   
				   img.setAttribute('src', 'Images/userplanet24.png');
			    else	   
  				   img.setAttribute('src', 'Images/enemyplanet24.png');
				   img.style.position='absolute';
				   img.style.left=-4;
				   img.style.top=-4;
        		   img.setAttribute('height', '24px');
         		   img.setAttribute('width', '24px');
         		   quadr.appendChild(img);
		   }
		   
		   
	       plandiv.appendChild(quadr);	
 		   	
		});
		
		
		$('.planets').css( 'cursor', 'pointer' );
		showSelectedPlanet();
			
}

function getFleetsInPosition(x,y){
  		cnt=0
	 	if (fleetarr==null) return 0;                
//		console.log('---------');
		$.each(fleetarr , function(index, value) {
                        if (value!=null)
			 if (value['coordx']==x && value['coordy']==y){
//				console.log(index+'.'+value['fltname']);
			   cnt++;
			 }
		});
//		console.log(cnt);
//		console.log('---------');
		return cnt;
}

function showAllFleets(x,y){
	
	 	if (fleetarr==null) return 0;
		rx={x:x,y:y};
		gridtomouse(rx);
		
		
		divallfleets=document.getElementById('divallfeets');
		if (divallfleets==null){
		  divallfleets=document.createElement('div');
		  divallfleets.id='divallfleets';
		  divallfleets.style.zIndex=20;
		  divallfleets.style.position='absolute';
		  divallfleets.style.left=rx.x;
		  divallfleets.style.top=rx.y;
		  divallfleets.style.width=100;
		  divallfleets.style.height=(630-557);
		  divallfleets.style.backgroundColor='#CD8C95';
		  document.getElementById('divleftarea').appendChild(divallfleets);
		}
		divallfleets.innerHTML='';
		n=0;
		if (selplanet>0){
  		  px=planetarr[selplanet]['coordx'];
		  py=planetarr[selplanet]['coordy'];
		  pownerid=planetarr[selplanet]['ownerid'];
		  if (px==x && py==y && pownerid==userid){
			  divfleet=document.createElement('div');	
			  divfleet.className='divfleets';
			  divfleet.setAttribute('data-id',-1);
			  divfleet.id='divplanet_'+selplanet;
			  divfleet.style.position='absolute';
			  divfleet.style.left=0;
			  divfleet.style.top=n++*20;
			  divfleet.style.width=100;
			  divfleet.style.height=20;
			  divfleet.innerText='---- Enter Planet ----';
			  divfleet.style.backgroundColor='blue';	   
			  divallfleets.appendChild(divfleet);			  
		  }
		}
		$.each(fleetarr , function(index, value) {
                        if (value==null) return true;
			if (value['coordx']==x && value['coordy']==y && value['ownerid']==userid){
			  divfleet=document.createElement('div');	
			  divfleet.className='divfleets';
			  divfleet.setAttribute('data-id',value['fltid']);
			  divfleet.id='divfleet_'+value['fltid'];
			  divfleet.style.position='absolute';
			  divfleet.style.left=0;
			  divfleet.style.top=n++*20;
			  divfleet.style.width=100;
			  divfleet.style.height=20;
			  divfleet.innerText=value['fltname'];
			  if (value['fltid']==selfleet)
				divfleet.style.backgroundColor='green';	   
			  divallfleets.appendChild(divfleet);
			}
		});
		$('.divfleets').css( 'cursor', 'pointer' );
		divallfleets.hidden=false;
		$('.divfleets').on('click', function(e){
			divallfleets.hidden=true;
			if ($(this).data('id')<0){
			  //enter planet
			  $('#quadr_'+selplanet).trigger('click');
			  return false;	
				
			}
			
			selfleet=$(this).data('id');
			
			//selfleetidx=$(this).data('idx');
			setAjaxSessionParam('selfleet',selfleet);	
			showSelectedFleet();
  		    refreshTab();
			
			//showAllFleets(x,y);
		});
}


function showSelectedFleet(){
		   fleetdiv=document.getElementById('fleetplandiv');
		   quadr=document.getElementById('selectedfleet');
//		   if (quadr!=null)
//  		      plandiv.removeChild(quadr);

		//	console.log('show selected');

		   if (selfleet>0)
 		      value=fleetarr[selfleet];
			  else return false;
		
			//console.log(value);
		   if (typeof value=='undefined') return false;
		   if (value==null) return false;
		   objReal={x:-1,y:-1};
		   getRealCoords({x:value['coordx'],y:value['coordy']},objReal);
		   ownername=value['username'];
   		   n=getFleetsInPosition(value['coordx'],value['coordy']);
		   if (n>1) ns=n+' fleets'; else ns='';
		   hint = value['fltname']+'('+ns+")\n["+value['coordx']+":"+value['coordy']+"]\n ("+ownername+")"; 
		   tid='selectedfleet';
		   
		   if (quadr==null){
  	          quadr=document.createElement("div");
			  newlycreated=true;
		   }
		   else newlycreated=false;
		   quadr.id=tid;
		   quadr.className='fleets '+tid;
		   quadr.setAttribute('data-id',value['fltid'] );
		   quadr.setAttribute('data-fleetcnt',n);		   
		   quadr.setAttribute('z-index',10 );
		   quadr.title=hint;
		   quadr.style.left=objReal.x-2;
		   quadr.style.top=objReal.y-2;
		   quadr.style.width=20;
   		   quadr.style.height=20;		   
		   quadr.style.position='absolute';
		   img=document.createElement('img');
	       img.setAttribute('src', 'Images/selectfleet24.png');
           img.setAttribute('height', quadr.style.width+'px');
           img.setAttribute('width', quadr.style.height+'px');
		   if (newlycreated){
  		     quadr.appendChild(img);
	         fleetdiv.appendChild(quadr);	
			 $('.selectedfleet').css( 'cursor', 'pointer' );	
		   }
			//if (n>1) showAllFleets(value['coordx'],value['coordy']);
			showRoutes();
	
}
	


function showFleets(){
	
		fleetdiv=document.getElementById('fleetplandiv');
 		if (fleetarr==null) return 0;
		$.each(fleetarr , function(index, value) {
		  // console.log(index+"."+value['coordx']+":"+value['coordy']);
                 if (value!=null){
		   objReal={x:-1,y:-1};
		   getRealCoords({x:value['coordx'],y:value['coordy']},objReal);
		   //console.log(index+"."+objReal.x+":"+objReal.y);
		   ownername=value['username'];		   
		   n=getFleetsInPosition(value['coordx'],value['coordy']);
		   if (n>1) ns=n+' fleets'; else ns='';
		   hint = value['fltname']+'('+ns+")\n["+value['coordx']+":"+value['coordy']+"]\n ("+ownername+")"; 
		   if (value['ownerid']==userid)		   
     		   imgname='fleet16aqua.png';
		   else	   
   		       imgname='fleet16red.png';
		   tid='fleet_'+value['fltid'];
		   
                   quadr=document.createElement("div");
		   quadr.id=tid;
		   quadr.className='fleets';
		   quadr.setAttribute('data-id',value['fltid'] );
		   quadr.setAttribute('data-fleetcnt',n);		   
		   quadr.title=hint;
		   quadr.style.left=objReal.x;
		   quadr.style.top=objReal.y;
		   quadr.style.width=16;
   		   quadr.style.height=16;
		   quadr.style.position='absolute';
		   img=document.createElement('img');
		   img.setAttribute('src', 'Images/'+imgname);
                   img.setAttribute('height', quadr.style.width+'px');
                   img.setAttribute('width', quadr.style.height+'px');
		   quadr.appendChild(img);
                   fleetdiv.appendChild(quadr);	
 		   	
		//   if (selfleet==value['fltid'])
		  //  selfleetidx=index;  		     
              }
		});
		
		
		$('.fleets').css( 'cursor', 'pointer' );
		if (selfleet>0)
     		showSelectedFleet();
			
	
	
}

function showMapGrid(){
	
  	solsyswidth=solsyssize*tilesizex;
	mapwidth=solsyswidth*3;
		

//	imgwidth=mapoffsetx+mapwidth+20+1;
//	imgheight=mapoffsety+mapwidth+1-128;

	clearCanvas('gridcanv');
	gridcanv=loadCanvas('griddiv','gridcanv',mapwidth+20,mapwidth+20);
	gridcanv.style.left=mapoffsetx-20;	
	gridcanv.style.top=mapoffsety-128-20;	
		
	createGrid(gridcanv,mapwidth,mapwidth,tilesizex*solsyssize,tilesizex*solsyssize,20,20,"#FFAEB9");
	
	//console.log(ssx);
	if (ssx-1<0) adn=1; else adn=0;
	for (x=0;x<3;x++){
		  tx=mapoffsetx+(solsyswidth/2)+(solsyswidth*x)-10;
		  ty=mapoffsety-128-7;
   		  tit=ssx-1+x+adn;
		  imagestring(gridcanv,12,tx,ty,tit,'white'); 	
	}
 	if (ssy-1<0) adn=1; else adn=0;
		for (y=0;y<3;y++){
		  tx=mapoffsetx-22;
		  ty=mapoffsety-128+(solsyswidth/2)+(solsyswidth*y);
		  tit=ssy-1+y+adn;
		  imagestring(gridcanv,12,tx,ty,tit,'white'); 	
		}
}


function movegridclicked(mx,my){


	if (selfleet>0) {	
	  fleet=fleetarr[selfleet];
      grdstart={x:fleet.coordx,y:fleet.coordy};
	  grdto={x:mx,y:my};
	  mousetogrid(grdto);
      //console.log(grdstart.x+':'+grdstart.y+"-->"+grdto.x+':'+grdto.y);
	  params="fltid="+fleet['fltid']+"&x="+grdto.x+"&y="+grdto.y;//send command to server move fleet
	  obj = getAjaxInfo(50,'map',params);//Move fleet
	  alert(obj.content); 
	  moveenabled=false;
	  hideMoveGrid();	  
 	  if (obj.content.substring(0,3)=='ETA') { //reload data
	    getMapData(false);
		refreshTab();	   
	  }

	}
}

function hideMoveGrid(){
	movediv=document.getElementById('movediv');	
	griddiv=document.getElementById('griddiv');
	movediv.hidden=true;
	griddiv.hidden=false;
	$('#btnmove').css("background-color","transparent");
}

function mousetogrid(obj){
 x=obj.x;
 y=obj.y;
 newssx=Math.max(ssx-1,0);
 newssy=Math.max(ssy-1,0);
 obj.x=Math.floor(x/tilesizex)+newssx*solsyssize;
 obj.y=Math.floor(y/tilesizex)+newssy*solsyssize;	
}

function gridtomouse(obj){
 x=obj.x;
 y=obj.y;
 newssx=Math.max(ssx-1,0);
 newssy=Math.max(ssy-1,0);
 obj.x=(x-newssx*solsyssize)   *tilesizex+mapoffsetx;
 obj.y=(y-newssy*solsyssize)   *tilesizex+mapoffsety-128;	
}

function centerquadrant(obj){
  obj.x=obj.x+Math.floor(tilesizex/2);
  obj.y=obj.y+Math.floor(tilesizex/2);  	
}


function showLineTo(grds,grde){
	rs={x:-1,y:-1};	re={x:-1,y:-1};
	rs.x=grds.x;rs.y=grds.y;
	re.x=grde.x;re.y=grde.y;
//	console.log(grds.x+":"+grds.y+"-->"+grde.x+":"+grde.y);

	gridtomouse(rs);
	gridtomouse(re);

//	console.log(rs.x+":"+rs.y+"-->"+re.x+":"+re.y);
	clearCanvas('linecanv');
	linecanv=loadCanvas('movediv','linecanv',mapwidth+20,mapwidth+20);
	centerquadrant(rs);centerquadrant(re);
	paintline(rs.x,rs.y,re.x,re.y,"linecanv",'movediv','white',true,[8]);	
	
}

function mycostfunc(curnode,neighbornode ){
	x1=curnode.x;y1=curnode.y;
	x2=neighbornode.x;y2=neighbornode.y;	

	x1=Math.floor(x1 / solsyssize);	y1=Math.floor(y1 / solsyssize);
	x2=Math.floor(x2 / solsyssize);	y2=Math.floor(y2 / solsyssize);
	
	soldist=(Math.abs(x1 - x2) + Math.abs(y1 - y2))*30;
	if (soldist>0) soldist=Math.floor(Math.sqrt(soldist));	
	return 1+soldist*30;	
	
}

function showLineTo2(grds,grde){ //using astar algorithm

	x=grds.x;y=grds.y;
    start = graph.nodes[x][y];	
	x=grde.x;y=grde.y;
    end = graph.nodes[x][y];
	console.log(grds.x+":"+grds.y+"--->"+grde.x+":"+grde.y);
    var path = astar.search(graph.nodes, start, end,true,astar.manhattan, mycostfunc);
  	if(!path || path.length == 0) {console.log('path not found');return 0;}

	clearCanvas('linecanv');
	linecanv=loadCanvas('movediv','linecanv',mapwidth+20,mapwidth+20);	
	rs={x:start.x,y:start.y};
	gridtomouse(rs);
	centerquadrant(rs);
	for (i=0;i<path.length-1;i++){
		re={x:path[i].x,y:path[i].y};   
    	gridtomouse(re);
		centerquadrant(re);
    	paintline(rs.x,rs.y,re.x,re.y,"linecanv",'movediv','white',i % 4==1,[]);		
		rs.x=re.x;		rs.y=re.y;
	}
		re={x:end.x,y:end.y};   
    	gridtomouse(re);
		centerquadrant(re);
    	paintline(rs.x,rs.y,re.x,re.y,"linecanv",'movediv','white',true,[]);		
	
        
		//rcs=getcoordsfromgrid(start.x+1,start.y+1,true);
	    //rce=getcoordsfromgrid(end.x+1,end.y+1,true);	



}


function showPath(mx,my){
	
	if (selfleet>0) {	
	  fleet=fleetarr[selfleet];
      grdstart={x:fleet.coordx,y:fleet.coordy};
      grdend={x:mx,y:my};
	  mousetogrid(grdend);
	  showLineTo2(grdstart,grdend);
	}
	
}

function showRoutes(fltid){

	    routecanv=loadCanvas('griddiv','routecanv',mapwidth+20,mapwidth+20);	
  	    if (routearr==null) return 0;
		$.each(routearr , function(index, value) {
		   if (typeof fltid!='undefined' && fltid!=value['fltid'])  return true;
		   rtid=value['rtid'];
		   waypoints=wayparr[rtid]; 
		   if (value['fltid']==selfleet) col='red'; else col='#7CCD7C';
	   	   $.each(waypoints , function(index, value) {
		      objReal={x:value['wx'],y:value['wy']};
			  gridtomouse(objReal);
			  centerquadrant(objReal);
			  if (index==0) objPre={x:objReal.x,y:objReal.y};
			   else {
				 paintline(objPre.x,objPre.y,objReal.x,objReal.y,"routecanv",'griddiv',col,(index % 4==1) || (index==waypoints.length-1),[1,1]);  
				 objPre.x=objReal.x;objPre.y=objReal.y;
			   }
			   
		   });
		});
		if (selfleet>0 && typeof fltid=='undefined')
    		showRoutes(selfleet);
}

function isgridpositionvalid(mx,my){		
 solsyswidth=solsyssize*tilesizex;
 mapwidth=solsyswidth*3;

 return (mx>=0 && mx<=mapwidth && my>=0 && my<=mapwidth);	
}

function showMoveGrid(){
	
	movediv=document.getElementById('movediv');
	griddiv=document.getElementById('griddiv');	

  	solsyswidth=solsyssize*tilesizex;
	mapwidth=solsyswidth*3;

	clearCanvas('movegridcanv');
	gridcanv=loadCanvas('movediv','movegridcanv',mapwidth+20,mapwidth+20);
	gridcanv.style.left=mapoffsetx-20;	
	gridcanv.style.top=mapoffsety-128-20;	
		
	createGrid(gridcanv,mapwidth,mapwidth,tilesizex,tilesizex,20,20,"#FFAEB9");

	createGrid(gridcanv,mapwidth,mapwidth,tilesizex*solsyssize,tilesizex*solsyssize,19,19,"lime");
	createGrid(gridcanv,mapwidth,mapwidth,tilesizex*solsyssize,tilesizex*solsyssize,20,20,"lime");
	createGrid(gridcanv,mapwidth,mapwidth,tilesizex*solsyssize,tilesizex*solsyssize,21,21,"lime");
	
	//console.log(ssx);
	if (ssx-1<0) adn=0; else adn=1;
	for (x=0;x<3*solsyssize;x++){
		  tx=mapoffsetx+(tilesizex/2)+(tilesizex*x)-10;
		  ty=mapoffsety-128-7;
   		  tit=(ssx-adn)*solsyssize+x;
		  if (tit<10) tit='0'+tit;				  		  
		  imagestring(gridcanv,8,tx,ty,tit,'white'); 	
	}
 	if (ssy-1<0) adn=0; else adn=1;
		for (y=0;y<3*solsyssize;y++){
		  
		  tx=mapoffsetx-18;
		  ty=mapoffsety-128+(tilesizex/2)+(tilesizex*y)+2;
		  tit=(ssy-adn)*solsyssize+y;
		  if (tit<10) tit='0'+tit;				  		  
		  imagestring(gridcanv,8,tx,ty,tit,'white'); 	
		}

	
	$('#movediv').css( 'cursor', 'crosshair' );	
	
	$('#movediv').unbind('click');
	$('#movediv').on('click', function(e){
		var offset = $(this).offset();
		mx=e.clientX - offset.left-22;
		my=e.clientY - offset.top-22;
		if (isgridpositionvalid(mx,my))
     	    movegridclicked(mx,my);									
	});				

	$('#movediv').unbind('mousemove');	
	$('#movediv').on('mousemove', function(e){
		var offset = $(this).offset();
		mx=e.clientX - offset.left-22;
		my=e.clientY - offset.top-22;
		if (isgridpositionvalid(mx,my))
		  showPath(mx,my);
		
	});				
	
	movediv.hidden=false;
	griddiv.hidden=true;
}

function getbuttonposition(idx){
  return 20+idx*48+4;	
}

function showActionButtons(){
	  actiondiv=document.getElementById('actiondiv');

//move button
      button=document.createElement("div");
      button.id='btnmove';
      button.className='button btnmove';
      button.setAttribute('data-id','move' );
      button.title='Move a fleet';
	  button.style.left=getbuttonposition(0)
	  button.style.top=2;
	  button.style.width=48;
   	  button.style.height=48;		   
	  button.style.position='absolute';
      img=document.createElement('img');
	  img.setAttribute('src', 'Images/routest48.png');
      img.setAttribute('width', button.style.width+'px');
      img.setAttribute('height', button.style.height+'px');
  	  button.appendChild(img);
	  actiondiv.appendChild(button);	
	  if (moveenabled)
   	    button.style.backgroundColor="red";



//map left button
      button=document.createElement("div");
      button.id='btnleft';
      button.className='button btnleft';
      button.setAttribute('data-id','mapleft' );
      button.title='Go left on quadrant';
	  button.style.left=getbuttonposition(1)+24;
	  button.style.top=2;
	  button.style.width=24;
   	  button.style.height=48;		   
	  button.style.position='absolute';
      img=document.createElement('img');
	  img.setAttribute('src', 'Images/arrowle.png');
      img.setAttribute('width', button.style.width+'px');
      img.setAttribute('height', button.style.height+'px');
  	  button.appendChild(img);
	  actiondiv.appendChild(button);	

//map up button
      button=document.createElement("div");
      button.id='btnup';
      button.className='button btnup';
      button.setAttribute('data-id','mapup' );
      button.title='Go up one quadrant';
	  button.style.left=getbuttonposition(2)-2;
	  button.style.top=2;
	  button.style.width=48;
   	  button.style.height=24;		   
	  button.style.position='absolute';
      img=document.createElement('img');
	  img.setAttribute('src', 'Images/arrowup.png');
      img.setAttribute('width', button.style.width+'px');
      img.setAttribute('height', button.style.height+'px');
  	  button.appendChild(img);
	  actiondiv.appendChild(button);	


//map down button
      button=document.createElement("div");
      button.id='btndown';
      button.className='button btndn';
      button.setAttribute('data-id','mapdown' );
      button.title='Go down one quadrant';
	  button.style.left=getbuttonposition(2);
	  button.style.top=24;
	  button.style.width=48;
   	  button.style.height=24;		   
	  button.style.position='absolute';
      img=document.createElement('img');
	  img.setAttribute('src', 'Images/arrowdn.png');
      img.setAttribute('width', button.style.width+'px');
      img.setAttribute('height', button.style.height+'px');
  	  button.appendChild(img);
	  actiondiv.appendChild(button);	

//map right button
      button=document.createElement("div");
      button.id='btnright';
      button.className='button btnright';
      button.setAttribute('data-id','mapright' );
      button.title='Go right one quadrant';
	  button.style.left=getbuttonposition(3);
	  button.style.top=2;
	  button.style.width=24;
   	  button.style.height=48;		   
	  button.style.position='absolute';
      img=document.createElement('img');
	  img.setAttribute('src', 'Images/arrowri.png');
      img.setAttribute('width', button.style.width+'px');
      img.setAttribute('height', button.style.height+'px');
  	  button.appendChild(img);
	  actiondiv.appendChild(button);	
	  
}

function putdesign(){
  maindiv=document.getElementById('divleftarea');
  griddiv=document.getElementById('griddiv');
  if (griddiv==null){
		  griddiv=document.createElement("div");
		  griddiv.id="griddiv";
		  griddiv.style.width = 510;
		  griddiv.style.height = 510;
		  griddiv.style.top=0;
		  griddiv.style.left=0;
		  griddiv.style.position='absolute';		  
		  griddiv.style.zIndex=1;
		//  griddiv.style.backgroundColor = 'red';
		  maindiv.appendChild(griddiv);
  }
  griddiv.innerHTML='';
  fleetplandiv=document.getElementById('fleetplandiv');
  if (fleetplandiv==null){
		  fleetplandiv=document.createElement("div");
		  fleetplandiv.id="fleetplandiv";
		  fleetplandiv.style.width = 510;
		  fleetplandiv.style.height = 510;
		  fleetplandiv.style.top=0;
		  fleetplandiv.style.left=0;
		  fleetplandiv.style.position='absolute';
		  fleetplandiv.style.zIndex=2;
		//  plandiv.style.backgroundColor = 'red';
		  maindiv.appendChild(fleetplandiv);	  
  }
  fleetplandiv.innerHTML='';
  movediv=document.getElementById('movediv');
  if (movediv==null){
		  movediv=document.createElement("div");
		  movediv.id="movediv";
	 	  movediv.style.width = 510;
		  movediv.style.height = 510;
		  movediv.style.top=0;
		  movediv.style.left=0;
		  movediv.opacity=0;
		  movediv.style.position='absolute';
		  movediv.style.zIndex=10;
		  movediv.hidden=true;
		//  plandiv.style.backgroundColor = 'red';
		  maindiv.appendChild(movediv);	  
  }
  movediv.innerHTML='';
//put action icons
  actiondiv=document.getElementById('actiondiv');
  if (actiondiv==null){
		  actiondiv=document.createElement("div");
		  actiondiv.id="actiondiv";
		  actiondiv.style.width = 510;
		  actiondiv.style.height = 50;
		  actiondiv.style.top=506;
		  actiondiv.style.left=0;
		  actiondiv.style.position='absolute';
		  actiondiv.style.zIndex=2;
		  //actiondiv.style.backgroundColor = 'red';
		  maindiv.appendChild(actiondiv);	  
	  
	  
  }
  actiondiv.innerHTML='';
}


function checkPlanetWithFleet(fltid){

	fleet=fleetarr[fltid];
	cx=fleet['coordx'];
    cy=fleet['coordy'];
	if (typeof maparr[cx]=='undefined') return false;
	pid= maparr[cx][cy];
	if (typeof pid=='undefined') return false;
	if (selplanet!=pid)
     	$('#quadr_'+pid).trigger('click');
	return true;
}

function setEvents(){
	
	
			$('.planets').on('click', function(e){
				
					pid=$(this).data('id');
					idx=$(this).data('idx');
					if (pid!=selplanet){
					  selplanet=pid;
					//set server session variable			
					   setAjaxSessionParam('selplanet',pid);		  
					  showSelectedPlanet();
					  refreshTab();
					  refreshPlanetInfo();
					 // $('.mytab').click();
					} else
					 window.open ('index.php?pg=planet','_self',false);					  
				});

			$('.selected').on('click', function(e){						
					 window.open ('index.php?pg=planet','_self',false);
			});
			
			
			$('.fleets').on('click', function(e){
					fltid=$(this).attr('data-id'); //data('id') not working???
					fcnt=$(this).attr('data-fleetcnt');
					//console.log('------');
				//	console.log($(this).attr('id'));
			     //	console.log(fltid+'=='+selfleet);
				 	oldselplanet=selplanet;
				 	f=checkPlanetWithFleet(fltid) && oldselplanet==selplanet;
					//console.log('fcnt='+fcnt);
					if (fltid==selfleet || f){					
						if (fcnt>1 || f) showAllFleets(fleetarr[fltid].coordx,fleetarr[fltid].coordy );
					}else{
					  selfleet=fltid;
					//set server session variable	
					
					   setAjaxSessionParam('selfleet',fltid);		  			  
					   showSelectedFleet();
					   refreshTab();
					 // $('.mytab').click();
					}					 			  
				});

			$('.button').on('click', function(e){
				butid=$(this).data('id');
				switch (butid){
				   case 'move':
				   				moveenabled=!moveenabled;
				   				if (moveenabled){
 									showMoveGrid();
								//	alert('hi');	
								
									
								}
								else hideMoveGrid();
								$(this).css("background-color","green");
				   			break;	
					case 'mapup':	
								ssy--;
								ssy=Math.max(1,ssy);								
								mapMoved();														
							break;
					case 'mapdown':							
								ssy++;
								ssy=Math.min(18,ssy);
								mapMoved();																						
							break;
					case 'mapleft':		
								ssx--;
								ssx=Math.max(1,ssx);
								mapMoved();						
							break;
					case 'mapright':							
								ssx++;
								ssx=Math.min(18,ssx);
								mapMoved();																						
							break;
					
					
				}
			});				

			$('.button').on('mouseover', function(e){
				$(this).css("background-color","green");
			});				

			$('.button').on('mouseout', function(e){
				if (moveenabled && $(this).attr('id')=='btnmove' ) 
				   $(this).css("background-color","red");
				else
 				   $(this).css("background-color","transparent");
			});				
			
}


function mapMoved(){

	getMapData(false);
	
}


function doafterload(){
   showMapGrid();
   showArea();
   if (moveenabled)
    showMoveGrid();
   showPlanets();
   showFleets();
   showRoutes();
   showActionButtons();
   
   setEvents();
   
   
   grid=matrix(solsyssize*20,solsyssize*20,1);

   graph = new Graph(grid);
	
	
}


var intid=0;
function whenload(){
   if (!loaded) { return 0;} 
   clearInterval(intid);
   doafterload();
	
}

function showMap(){
	
	
   putdesign();

	n=0;
    if(!loaded) 
	 intid=setInterval(whenload,30)
    else doafterload();
}


function getMapData(fullrefresh){
      if ((loaded && (typeof ssx != 'undefined')) && !fullrefresh)
  	     obj = getAjaxInfo(30,'map','ssx='+ssx+"&ssy="+ssy,'mydata'); 	
	  else
	   obj = getAjaxInfo(30,'map','p=0','mydata'); 	
       //console.log('getMapData');
	  showMap();
	
}

function refreshMap(){
	getMapData(true)   	
}

function refreshTab(){
  if (mainpage=='map') {
//   console.log('seltab='+selectedTab);
    tabpressed(null,selectedTab);	
	
  }
	
}