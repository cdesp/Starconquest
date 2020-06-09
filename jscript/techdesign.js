// JavaScript Document
var tmnow = new Date();
tmdif = tmnow - servertime * 1000;
//   console.log('srvtim='+servertime);
//  console.log('now='+tmnow);
//  console.log('srvdif='+tmdif);


loadCSS('CSS/techdesign.css');
var errorload = false;
loaded = true;
mytestint = 0;
//console.log('loading jscript');
/*
 $.getScript( "jscript/graphics.js" )
 .done(function( script, textStatus ) {
 //  console.log( 'graphics.js loaded:'+textStatus );
 loaded=true;
 })
 .fail(function( jqxhr, settings, exception ) {
 errorload=true;
 console.log( "Triggered ajaxError handler. :> "+exception );
 });
 */
var container = null;
var rectcnt = 0;
var upgarr = new Array();
upgnxt = 0;

function addupgradebutton(tid) {
    html =
            '<div class="techbutton" >' +
            '<form name="fupg" method="post">' +
            '<button id="upg-tech" name="submit" value="upg" ><img src="' + getHomeURL() + '/Images/upgrade_tech.png"> ' +
            '<input type="number" name="techid" value="' + tid + '" hidden/>' +
            '</button></form></div>';
    //console.log(html);
    return html;
}

function gettimenice(itable) {
    if (itable['techtimedays'] > 0)
        return itable['techtimedays'] + '  days ' + itable['techtimehours'] + ' hours';
    else
        return itable['techtimehours'] + ' hours';

}

function makerect(id, px, py, title, text, footer, itable) {
    rectcnt++;
    classnm = 'techrect';
//	console.log(rectcnt+"-->"+title);

    tt = document.createElement('div');
    tt.setAttribute('class', classnm);
    tt.setAttribute('id', id);
    t = document.createElement('div');
    t.setAttribute('class', classnm + 'top');
    c = document.createElement('div');
    c.setAttribute('class', classnm + 'cont');
    b = document.createElement('div');
    b.setAttribute('class', classnm + 'bot');
    b.innerHTML = "";

    tt.appendChild(t);
    tt.appendChild(c);
    tt.appendChild(b);
    container.appendChild(tt);
    tt.style.opacity = 90;
    tt.style.filter = 'alpha(opacity=90)';
    tt.style.zIndex = 50;

    c.style.width = 200;
    c.style.height = 70;
    tt.style.left = px;
    tt.style.top = py;
    t.innerHTML = '<div class="techid">T.ID:' + itable['techid'] + ') </div>'
            + '<span class="titcolor">' + title + '</span>';


    c.innerHTML = '<div class="TP">' + itable['techpoints'] + '</div>'
            + '<div class="clk">' + gettimenice(itable) + ' </div>'
            + '<div class="techtxt">' + text + '</div>';

    //console.log('TechID='+itable['techid']);       
    //console.log('B tim:'+itable['buildtime']);              
    if (itable['buildtime'] != null) {
        if (itable['buildtime'] == 0) {
            tx = 'Discovered';
            b.innerHTML += '<div class="techfoot2">' +
                    footer + ' ' + tx + '</div>';
        } else {

            timend = parseInt(itable['buildtime']) * 1000;
            var timnow = new Date();
            timdif = timend - timnow;

            tmstr = getunixdate(timdif);

            // upgarr[upgnxt]=new Array();
            // upgarr[upgnxt]['time']=timend;		  		  
            upgarr[upgnxt] = timend;
            //  console.log('TIMENOW='+timnow);
            //  console.log('TIMEEND='+timend);
            //  console.log('TIMEDIF='+timdif);
            //  console.log(tmstr);
            tx = '<DIV  id="tm_' + upgnxt + '">Discovered in: ' + tmstr + '</DIV>';
            upgnxt = upgnxt + 1;
            //	console.log(tx);
            b.innerHTML += '<div class="techfoot2"><div class="tchfooter">' +
                    footer + '</div>  ' + tx + '</div>';
        }
    } else
    {
        b.innerHTML = addupgradebutton(itable['techid']);
        b.innerHTML += '<div class="techfoot">' +
                footer + '</div>';
        //console.log(footer);

    }

}




function updateupgradetechtimes() {
    dorefr = false;

    var tmnow = new Date();
    //console.log('NOW='+tmnow);
    //console.log(tmnow);
    //console.log(tmnow.getDate());
    tmnow = new Date(tmnow.getTime() + tmdif);
    //  console.log('TIME DIF='+tmdif);

    $.each(upgarr, function (index, value) {
        tmend = parseInt(value);//*1000;
        // console.log('nowadd='+tmnow);
        // console.log(tmend);
        timdif = tmend - tmnow;
        tmstr = getunixdate(timdif);
        // console.log(tmstr);
        if (tmend <= tmnow) {
            dorefr = true;
        }
        n1 = document.getElementById('tm_' + index);
        if (n1 != null) {
            //console.log('found tm_'+index);
            n1.innerHTML = 'Discovered in: ' + tmstr;
        } else
            console.log(index + ' not found???');
    });

    if (dorefr) {
        //alert('refresh');

        //window.clearInterval(refid);
        //refid=null;
        console.log('refresh page');
        //refreshData();	 	    
        location.reload();
    }

    return dorefr;
}


var refid = null;
var refintrval = 1000;
function setTechUpgInfoTimer() {
    if (refid != null)
        window.clearInterval(refid);
    refid = setInterval(function () {
        updateupgradetechtimes();
    }
    , refintrval);
}


var difh = 130;
var difw = 360;
var rectcr = 0;
var posarr = new Array();
var maxpos = 0;

function techrec(parid, pos) {
    if (typeof posarr[pos] == 'undefined')
        posarr[pos] = new Array();
//	console.log('-------------------');
//	console.log('Start Check for previd='+parid);
//	console.log('array max pre'+posarr[pos].length);
    var v = 0;
    if (posarr[pos] != null)
        v = posarr[pos].length;

    var vstart = v;
    //find all techs in that pos 
    $.each(techArr, function (index, value) {
        if (value['previd'] == parid) {
            //console.log('adding '+value['techid']);
            posarr[pos][v] = value;
            v++;
        }
    });
    //console.log('array max aft'+posarr[pos].length);	

    if (v == vstart) {
        maxpos = Math.max(maxpos, pos - 1);
        //console.log('maxpos'+maxpos);

    } else //get the childrens of those techs
    {
        //	 console.log('Position '+pos); 
        var cnt = 1;
        $.each(posarr[pos], function (index, value) {
            //   console.log('pos:'+pos);
            // console.log('cnt:'+cnt++);
            // console.log('index:'+index); 
            //  console.log('check for '+value['techid']);
            if (index >= vstart) // skip those cause we already checked them
                techrec(value['techid'], pos + 1);
            // console.log('posaft:'+pos);
            // console.log('indexaft:'+index); 
        });
    }

    //console.log('END Check for previd='+parid);
    //console.log('');
}



function showtech() {

    container = getElementByName('techtabmain');
    container.innerHTML = '';

    maxpos = 0;
    rectcr = 0;
    posarr.length = 0;
    techrec(0, 0);
    //show all tech in that pos

//	console.log('show rects');
//	console.log('maxpos:'+maxpos);
    //show rectangles
    for (pos = 0; pos <= maxpos; pos++) {
        var horiz = 10 + difw * pos;
        var i = 0;
        $.each(posarr[pos], function (index, value) {
            lnt = posarr[pos].length;
            offset = (4 - lnt) * 60;
            vert = 10 + difh * i + offset;
            makerect(1 + pos * 5 + i, horiz, vert, value['techname'], value['effcomment'], ++rectcr + ")", value);
            i++;
        });
    }
    ;

    //console.log('show Connections');
    //show connections
    for (pos = 0; pos <= maxpos; pos++) {
        var horiz = 10 + difw * pos + 200;
        var i = 0;
        $.each(posarr[pos], function (index, value) {
            lnt = posarr[pos].length;
            offset = (4 - lnt) * 60;
            vert = 10 + difh * i + offset + 50;
            connecttochilds(horiz, vert, pos, value);
            i++;
        });
    }
    ;


    //console.log('RCTS:'+rectcnt);

}

function connecttochilds(sx, sy, p, v) {
    //console.log('Connecttochilds');
    loadCanvas("techtabmain", "concanv", 0, 0);
    id = v['techid'];
    var horiz = 10 + difw * (p + 1);
    var i = 0;
    $.each(posarr[p + 1], function (index, value) {
        //console.log('previd='+value['previd']+" id="+value["techid"]+"   parnt="+id);
        if (value['previd'] == id) {
            lnt = posarr[p + 1].length;
            offset = (4 - lnt) * 60;
            vert = 10 + difh * i + offset;
            ex = horiz - 5;
            ey = vert + 50;
            paintline(sx, sy, ex, ey, "concanv", "techtabmain", '#FF8000', true, [], 6, 20);
            //console.log('arrow='+i);
            //console.log('sx='+sx+' sy='+sy+' ex='+ex+' ey='+ey);

        }
        i++;
    });

}

function testLoaded() {
//	console.log('testing loaded');
    if (loaded != true) {
        return false;
    }
//	console.log('loaded true');
    clearInterval(mytestint);
    showtech();
    setTechUpgInfoTimer();

    return true;
}

function initform() {
    //console.log('init form');	
    mytestint = setInterval(testLoaded, 20);
    //showtech();
}

function getTechData(fullrefresh) {
    //do 
    //if (errorload==true) 
    //{
    //  alert('Error loading graphics');
    //return false;
    //}

    //while (loaded!=true);

    obj = getAjaxInfo(30, 'tech', 'p=0', 'mydata');
    console.log('show tech');


}

function refreshData() {

    getTechData(true);
    refreshPlanetInfo();
}
