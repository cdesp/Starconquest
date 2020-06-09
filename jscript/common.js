// JavaScript Document

loadCSS = function(href) {

  var cssLink = $("<link>");
  $("head").append(cssLink); //IE hack: append before setting href

  cssLink.attr({
    rel:  "stylesheet",
    type: "text/css",
    href: href
  });

};


//name:"your name" for data
function getAjaxData(remurl,datasend ){
    retdata="no data";
    //alert(remurl);
    //alert(datasend);
    //console.log(remurl);
    $.ajax({
  		url: remurl, //give your URL here
		async: false,
  		data: datasend , //(optional) if you wish you can send this data to server, just like this.
  		success: function(data){
    		retdata=data;
			//$('body').prepend(data); //here is your data
			
            }
    });	
	
    return retdata;
}

function getAjaxScript(remurl ){
  $.ajax({
    url: remurl,
	async: false,
    dataType: "script",	
    success:  function(){
			
  		}
  });
}

//params and scriptcode is not necessary
function getAjaxInfo(inf,frmpg,params,scrname,clearDbg = false){
	
   varsend="info="+inf;
   varsend="pg="+frmpg+"&"+varsend ;		
   if  (typeof(params)==='undefined') {       
   } else varsend=varsend+"&"+params;   
//   alert(varsend);
   remurl="Core/getajaxpage.php";
   if (inf<=0) console.log(varsend);
   data=getAjaxData(remurl,varsend);
   if (inf<=0) console.log('DATA=['+data+']');
 // console.log("data in [");
  //console.log(data);
  //console.log("]");
  if (data=='no data' || data=='') {
     console.log('ERROR Fetching page!!! params='+varsend+' url='+remurl); 
     return false;
  }  
  try {
   var obj = jQuery.parseJSON(data);
  }
  catch(err) {
      console.log (err);
 console.log("data in [");
console.log(data);
console.log("]");
       
      return null;
  }
   if (clearDbg) clearAjaxDebug(); 
   //if  (typeof(params)==='undefined') {}
   
     if (obj.hasOwnProperty('scriptcode') && obj.scriptcode!='') {
	     addScriptToPage(obj.scriptcode,scrname);		 
	 }
     if (obj.hasOwnProperty('debugdata') && obj.debugdata!=null) {
	    divobj=document.getElementById('debug'); 
            if (!clearDbg) divobj.insertAdjacentHTML( 'beforeend',  '<BR>FROM PAGE:'+frmpg+' PARAMS:'+params+'<BR>' );	
	    divobj.insertAdjacentHTML( 'beforeend',  obj.debugdata+'<BR>');	
            //console.log('DBG='+divobj.innerHTML);
            //alert('ok:'+inf+' fpg='+frmpg+' scr='+scrname);
	 }
         
     if (obj.hasOwnProperty('debugbottom') && obj.debugbottom!=null) {
	    divobj=document.getElementById('bottomout'); 
            if (!clearDbg) divobj.insertAdjacentHTML( 'beforeend',  '<BR>FROM PAGE:'+frmpg+' PARAMS:'+params+'<BR>' );	
	    divobj.insertAdjacentHTML( 'beforeend',  obj.debugbottom+'<BR>');	
            //console.log('DBG='+divobj.innerHTML);
            //alert('ok:'+inf+' fpg='+frmpg+' scr='+scrname);
	 }
         
	
   return obj;	
	
}

function clearAjaxDebug(){
    divobj=document.getElementById('debug'); 
    divobj.innerHTML = "";	
    
}


function getAjaxSessionParam(param){	
   varsend="sparam="+param;
   remurl="Core/getajaxpage.php";
  // alert(varsend);
   
   data = getAjaxData(remurl,varsend);	
   var obj = jQuery.parseJSON(data);   
   return obj.sparam;
}


function setAjaxSessionParam(paramname,value){
	
	//set server session variable			
   varsend="sesvar="+paramname+"&"+paramname+"="+value;
   remurl="Core/setsessionvar.php";			
   data=getAjaxData(remurl,varsend);
	
}

function getAjaxParam(pg,param){	
   varsend="param="+param;
   varsend="info=98&"+varsend
   varsend="pg="+pg+"&"+varsend;			   
   remurl="Core/getajaxpage.php";
   data = getAjaxData(remurl,varsend);
   try {
    var obj = jQuery.parseJSON(data);   
   }
   catch(err) {
      console.log (err);
      return null;       
   }
   return obj.param;
}


function addScriptToPage(scr,scname){
	
   t=document.getElementById(scname);
   if (t!=null) 
     t.parentNode.removeChild(t);
	 
	
   var g = document.createElement('script');
   g.id=scname;
   var s = document.getElementsByTagName('script')[0];
   g.text = scr;
 //  console.log(g.id);
 //  console.log(g.text);
 //console.log('add script '+scname);
   s.parentNode.insertBefore(g, s);			   
	
}

function getElementByName(Name){

  elm=document.getElementsByName(Name);
  return elm[0];
	
	
}

function getElementValByName(Name){

  elm=document.getElementsByName(Name);
  return elm[0].innerHTML;
}

function setElementValByName(Name,NewVal){

  elm=document.getElementsByName(Name);
  elm[0].innerHTML=NewVal;
	
	
}

function toNumber(V){
 if (typeof V == 'undefined') return 0;
	
 V=V.replace(".","");
 if (V=="") V=0;
 return parseInt(V);	
}


function formatNumber(number){
						var num = new NumberFormat();
						num.setInputDecimal('.');
						num.setNumber(number); //<<-- set the number
						num.setPlaces('0', false);
						num.setCurrencyValue('$');
						num.setCurrency(false);
						num.setCurrencyPosition(num.LEFT_OUTSIDE);
						num.setNegativeFormat(num.LEFT_DASH);
						num.setNegativeRed(false);
						num.setSeparators(true, '.', ',');
						return num.toFormatted();						
						
}


function changeLabelText(lblName,lblValue){
	
   lbl=document.getElementsByName(lblName);
   lbl[0].innerHTML=lblValue;
	
} 


function changeQueryVariable(keyString, replaceString) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == keyString) {
            vars[i] = pair[0] + "=" + replaceString
        }
    }
    return vars.join("&");

}

function getBaseURL() {
var url = location.href;  // entire url including querystring - also: window.location.href;
var baseURL = url.substring(0, url.indexOf('/', 14));


if (baseURL.indexOf('http://localhost') != -1) {
    // Base Url for localhost
    var url = location.href;  // window.location.href;
    var pathname = location.pathname;  // window.location.pathname;
    var index1 = url.indexOf(pathname);
    var index2 = url.indexOf("/", index1 + 1);
    var baseLocalUrl = url.substr(0, index2);

    return baseLocalUrl + "/";
}
else {
    // Root Url for domain name
    return baseURL + "/";
}
}

function getMainURL(){
	var s=getBaseURL();
	return s+window.location.pathname.substr(1, 40);	
}

function getHomeURL(){
	var s=getBaseURL();
	t1=window.location.pathname;	
  var index1 = t1.indexOf("/");
  var index2 = t1.indexOf("/", index1 + 1);
  t1=t1.substr(1,index2);
//  console.log(t1);
	s= s+t1;
//	console.log(s);
	return s;
	
}


function createArray(length) {
    var arr = new Array(length || 0),
        i = length;

    if (arguments.length > 1) {
        var args = Array.prototype.slice.call(arguments, 1);
        while(i--) arr[length-1 - i] = createArray.apply(this, args);
    }

    return arr;
}

function matrix( rows, cols, defaultValue){

  var arr = [];

  // Creates all lines:
  for(var i=0; i < rows; i++){

      // Creates an empty line
      arr.push([]);

      // Adds cols to the empty line:
      arr[i].push( new Array(cols));

      for(var j=0; j < cols; j++){
        // Initializes:
        arr[i][j] = defaultValue;
      }
  }

return arr;
}


function scrollToView(elm){

$("#"+elm).scrollintoview({
    duration: 500,
    direction: "vertical",
    complete: function() {
        // highlight the element so user's focus gets where it needs to be
    }
});	
//	$('#'+elm)[0].scrollIntoView();	
	
//   elm=document.getElementById(elm);
  // if (elm!=null)	
	//  elm.scrollIntoView();	
}


function scrollToElement(pageElement) {    
    var positionX = 0,         
        positionY = 0;    

    while(pageElement != null){        
        positionX += pageElement.offsetLeft;        
        positionY += pageElement.offsetTop;        
        pageElement = pageElement.offsetParent; 
		console.log(pageElement);
		console.log( positionY+":"+positionY);      
        window.scrollTo(positionX, positionY);    
    }
}

function parentscrollToElement(pageElement) {    
    var positionX = 0,         
        positionY = 0;    

        positionX += pageElement.offsetLeft;        
        positionY += pageElement.offsetTop;        
        pageElement = pageElement.offsetParent;        
		console.log(pageElement);
		console.log( positionY+":"+positionY);      
		
        window.scrollTo(positionX, positionY);    
}

function hasClass(ele,cls) {
    return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function addClass(ele,cls) {
    if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
    if (hasClass(ele,cls)) {
        var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
        ele.className=ele.className.replace(reg,' ');
    }
}

   function formatTime(seconds) {
	        seconds = Math.round(seconds);
    	    minutes = Math.floor(seconds / 60);
    	    hours = Math.floor(minutes / 60);       	
    	    days = Math.floor(hours / 24);
					
			minutes -= hours * 60;
			hours -= days * 24;
	        seconds = Math.floor(seconds % 60);

        	days = (days >= 10) ? days : '0' + days;
			hours = (hours >= 10) ? hours : '0' + hours;
        	minutes = (minutes >= 10) ? minutes : '0' + minutes;			
    	    seconds = (seconds >= 10) ? seconds : '0' + seconds;

			s='';
			if (days>0) s=s+days + 'd ';
			if (hours>0) s=s+hours + 'h ';
			if (minutes>0) s=s+minutes + 'm ';
			s=s+seconds + 's';
        	return  s;
	    }			


function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}

function getunixdate(dt){
	
var date = new Date(dt);

//var days = date.getDay();
// hours part from the timestamp
msecs=dt;
secs = msecs / 1000;
mins = secs / 60;
hours = mins / 60;
days = Math.floor(hours / 24);

hours=Math.floor(hours-days*24);
mins=Math.floor(mins-(hours *60+days*24*60));
secs=Math.floor(secs-(mins*60+hours *60*60+days*24*60*60));


return days + "d "+hours + "h "+mins + "m "+secs + "s ";


	
}