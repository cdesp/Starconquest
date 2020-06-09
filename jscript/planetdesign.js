// JavaScript Document

var loaded = true;
selbuilding = -1;
mainpage = '';
tmnow = new Date();
tmdif = tmnow - servertime * 1000;
// console.log('srvtim='+servertime);
// console.log('now='+tmnow);
// console.log('srvdif='+tmdif);

/*
 
 $.getScript( "jscript/graphics.js" )
 .done(function( script, textStatus ) {
 //  console.log( textStatus );
 loaded=true;
 })
 .fail(function( jqxhr, settings, exception ) {
 console.log( "Triggered ajaxError handler. :> "+exception );
 });
 
 */

function getbuildname(pid, btid) {

    return 'build_' + pid + "_" + btid;

}

function getbldname(btid) {
    return getbuildname(selplanet, btid);
}

function getbuildingobj(btid) {
    return $('#' + getbldname(btid));

}


function showSelectedBuilding(newsel, obj) {
    oldtid = getbldname(selbuilding);
    newtid = getbldname(newsel);
    image = $('#' + oldtid).data('img') + '.png';
    selimage = $(obj).data('img') + '_sel.png';


    $('#' + oldtid).css("background-image", "url('Images/buildings/" + image + "')");
    $('#' + newtid).css("background-image", "url('Images/buildings/" + selimage + "')");

    selbuilding = newsel;

    buildinfo = document.getElementById('buildinfo');
    if (buildinfo != null) {
        bldarr = buildingsArr[selbuilding];
        buildinfo.style["font-size"] = 14;
        buildinfo.innerHTML = '<b>' + bldarr['bname'] + '</b><br><br>' + bldarr['btcomments'];
        console.log(buildinfo.innerHTML);
    } else
        console.log('buildinfo not found');


}


function showBuildings() {


    obj = getAjaxInfo(0, 'planet', 'p=0', 'mydata');
    planetdiv = document.getElementById('planetdiv');
    console.log('showbuild');
    planetdiv.innerHTML = obj.content;
}


function getbuttonposition(idx) {
    return 20 + idx * 48 + 4;
}

function showActionButtons() {
    actiondiv = document.getElementById('actiondiv');

//move button
    button = document.createElement("div");
    button.id = 'btnupgrade';
    button.className = 'button';
    button.setAttribute('data-id', 'upgrade');
    button.setAttribute('data-hint', 'Upgrade a Building');
//      button.title='Upgrade a Building';	 
    button.style.left = getbuttonposition(0)
    button.style.top = 2;
    button.style.width = 48;
    button.style.height = 48;
    button.style.position = 'absolute';
    img = document.createElement('img');
    img.setAttribute('src', 'Images/UpgBld.png');
    img.setAttribute('width', button.style.width + 'px');
    img.setAttribute('height', button.style.height + 'px');
    button.appendChild(img);
    actiondiv.appendChild(button);
//	  if (moveenabled)
//   	    button.style.backgroundColor="red";



//map left button
    button = document.createElement("div");
    button.id = 'btnleft';
    button.className = 'button btnleft';
    button.setAttribute('data-id', 'mapleft');
    //  button.title='Go left on quadrant';
    button.setAttribute('data-hint', 'Go left on quadrant');
    button.style.left = getbuttonposition(1) + 24;
    button.style.top = 2;
    button.style.width = 24;
    button.style.height = 48;
    button.style.position = 'absolute';
    img = document.createElement('img');
    img.setAttribute('src', 'Images/arrowle.png');
    img.setAttribute('width', button.style.width + 'px');
    img.setAttribute('height', button.style.height + 'px');
    button.appendChild(img);
    actiondiv.appendChild(button);

//map up button
    button = document.createElement("div");
    button.id = 'btnup';
    button.className = 'button btnup';
    button.setAttribute('data-id', 'mapup');
    //   button.title='Go up one quadrant';
    button.setAttribute('data-hint', 'Go up one quadrant');
    button.style.left = getbuttonposition(2) - 2;
    button.style.top = 2;
    button.style.width = 48;
    button.style.height = 24;
    button.style.position = 'absolute';
    img = document.createElement('img');
    img.setAttribute('src', 'Images/arrowup.png');
    img.setAttribute('width', button.style.width + 'px');
    img.setAttribute('height', button.style.height + 'px');
    button.appendChild(img);
    actiondiv.appendChild(button);


//map down button
    button = document.createElement("div");
    button.id = 'btndown';
    button.className = 'button btndn';
    button.setAttribute('data-id', 'mapdown');
    //   button.title='Go down one quadrant';
    button.setAttribute('data-hint', 'Go down one quadrant');
    button.style.left = getbuttonposition(2);
    button.style.top = 24;
    button.style.width = 48;
    button.style.height = 24;
    button.style.position = 'absolute';
    img = document.createElement('img');
    img.setAttribute('src', 'Images/arrowdn.png');
    img.setAttribute('width', button.style.width + 'px');
    img.setAttribute('height', button.style.height + 'px');
    button.appendChild(img);
    actiondiv.appendChild(button);

//map right button
    button = document.createElement("div");
    button.id = 'btnright';
    button.className = 'button btnright';
    button.setAttribute('data-id', 'mapright');
    // button.title='Go right one quadrant';
    button.setAttribute('data-hint', 'Go right one quadrant');
    button.style.left = getbuttonposition(3);
    button.style.top = 2;
    button.style.width = 24;
    button.style.height = 48;
    button.style.position = 'absolute';
    img = document.createElement('img');
    img.setAttribute('src', 'Images/arrowri.png');
    img.setAttribute('width', button.style.width + 'px');
    img.setAttribute('height', button.style.height + 'px');
    button.appendChild(img);
    actiondiv.appendChild(button);

}

function putdesign() {
    maindiv = document.getElementById('divleftarea');
    planetdiv = document.getElementById('planetdiv');
    if (planetdiv == null) {
        planetdiv = document.createElement("div");
        planetdiv.id = "planetdiv";
        planetdiv.style.width = 600;
        planetdiv.style.height = 510;
        planetdiv.style.top = 0;
        planetdiv.style.left = 0;
        planetdiv.style.position = 'absolute';
        planetdiv.style.zIndex = 1;
        //  griddiv.style.backgroundColor = 'red';
        maindiv.appendChild(planetdiv);
    }
    planetdiv.innerHTML = '';

//put action icons
    actiondiv = document.getElementById('actiondiv');
    if (actiondiv == null) {
        actiondiv = document.createElement("div");
        actiondiv.id = "actiondiv";
        actiondiv.style.width = 510;
        actiondiv.style.height = 50;
        actiondiv.style.top = 506;
        actiondiv.style.left = 0;
        actiondiv.style.position = 'absolute';
        actiondiv.style.zIndex = 2;
        //actiondiv.style.backgroundColor = 'red';
        maindiv.appendChild(actiondiv);


    }
    actiondiv.innerHTML = '';
}

function getHint(ths, wid) {
    txt = $(ths).data('hint');
    img = document.createElement('img');
    img.setAttribute('src', 'Images/info.png');
    img.setAttribute('width', '32px');
    img.setAttribute('height', '32px');
    img.setAttribute('display', 'inline-block');
    img.style.cssFloat = 'left';
    img.style.paddingTop = 5;
    d1 = document.createElement('div');
    d1.appendChild(img);
//  d1.innerHTML+="TESTING";
    d1.style.height = 'auto';
    // d1.style.background= '#ff0000';
    d1.style.cssFloat = 'left';


    d2 = document.createElement('div');
    d2.className = 'tempclass';
    d2.innerHTML = txt;
    d2.style.height = 'auto';
    d2.style.width = wid;//-70;
    d2.style.cssFloat = 'left';
//  d2.style.background= '#00ff00';
    d2.style.paddingLeft = 10;



    d3 = document.createElement('div');
    d3.style.clear = 'both';

//  d=document.createElement('div');

//  d.appendChild(d1);
//  d.appendChild(d2);
//  d.appendChild(d3);

    //document.getElementById('actiondiv').appendChild(d);

//  d2h= $('.tempclass').height();
    // console.log(d2h);
    //maxh=Math.max(d2h,32);
    //console.log(maxh);
    //d1.style.height=maxh;
    //d2.style.height=maxh;


//  htm="<div style='float:left'>"+img.outerHTML +"</div><div style='float:left'><b>"+txt+"</b></div>";
//  htm="<div style='float:left' >"+img.outerHTML +"</div><div style='float:left;margin:10' ><b>"+txt+"</b></div><div style='clear: both;'></div>";

    return d1.outerHTML + d2.outerHTML + d3.outerHTML;
// 	return d.outerHTML;

}


function setEvents() {


    $('.buildingclass').on('click', function (e) {

        btid = $(this).data('id');
        if (btid != selbuilding) {

            //set server session variable			
            setAjaxSessionParam('selbuilding', btid);
            showSelectedBuilding(btid, $(this));
            refreshTab();
            //  refreshPlanetInfo();					 
        }
    });



    $('.button').on('click', function (e) {
        butid = $(this).data('id');
        switch (butid) {
            case 'upgrade':
                if (selbuilding != '-1') {
                    bld = getbuildingobj(selbuilding);
                    console.log(bld);
                    bid = selbuilding;
                    obj = getAjaxInfo(91, 'planet', 'bid=' + bid, 'planprodscr');
                    alert(obj.content);
                    refreshData();
                } else
                    alert('Please select a building');
                break;
            case 'mapup':
                break;
            case 'mapdown':
                break;
            case 'mapleft':
                break;
            case 'mapright':
                break;


        }
    });

    $('.button').on('mouseover', function (e) {
        $(this).css("background-color", "green");
        tooltip.show(getHint(this, 200), 200);
    });

    $('.button').on('mouseout', function (e) {
        tooltip.hide();
//				if (moveenabled && $(this).attr('id')=='btnmove' ) 
//				   $(this).css("background-color","red");
//				else
        $(this).css("background-color", "transparent");
    });


    $('.buildingclass').on('mouseover', function (e) {
        $(this).data('img') + 'ovr.png';
        $(this).css("background-image", "url('Images/buildings/" + $(this).data('img') + '_ovr.png' + "')");
        tooltip.show(getHint(this, 100), 100);
    });

    $('.buildingclass').on('mouseout', function (e) {
        if ($(this).data('id') == selbuilding)
            $(this).css("background-image", "url('Images/buildings/" + $(this).data('img') + '_sel.png' + "')");
        else
            $(this).css("background-image", "url('Images/buildings/" + $(this).data('img') + '.png' + "')");
        tooltip.hide();
    });


}

function createTextLabel(label, parentid, tlid, x, y, width, height) {
    tl = document.getElementById(tlid);
    if (tl == null) {
        tl = document.createElement("div");
        tl.id = tlid;
        tl.className = 'textlabel';
        tl.title = '';
        tl.style.width = width;
        tl.style.height = height;
        tl.style.zIndex = 12;
        tl.style.left = x;
        tl.style.top = y;
        tl.style.position = 'absolute';
    }
    tl.innerHTML = label;
    parent = document.getElementById(parentid);
    parent.appendChild(tl);

}



function setBuildingInfo(bldarr) {
    console.log(bldarr['btid']);
    bx = parseInt(bldarr['dsgn_x']) + 20;
    by = parseInt(bldarr['dsgn_y']) + 132;
    console.log(bx + ":" + by);
    if (bldarr['baction'] == 1) {
        tmend = parseInt(bldarr['bactfinished']) * 1000;
        var tmnow = new Date();
        tmdif = tmend - tmnow; //TODO:fix this not to use var tmdif
        tmstr = getunixdate(tmdif);
        console.log(tmstr);
        createTextLabel(tmstr, 'planetdiv', 'blbl_' + bldarr['btid'], bx, by, 95, 12);
    }
    $('.textlabel').css('font-size', '10px');
    $('.textlabel').css('color', '#404040');
    $('.textlabel').css("background-color", "aqua");
    //todo: set hint to resources needed to upgrade
    build = document.getElementById('build_' + bldarr['pid'] + '_' + bldarr['btid']);
    if (build != null) {
        lvl = parseInt(bldarr['blevel']) + 1;
        if (lvl < parseInt(bldarr['bmaxlevel'])) {
            gld = formatNumber(parseInt(bldarr['goldupg']));
            met = formatNumber(parseInt(bldarr['metalumupg']));
            trt = formatNumber(parseInt(bldarr['tritiumupg']));
            tm = " Time : " + bldarr['daysupg'] + "d " + bldarr['hoursupg'] + "h " + bldarr['minsupg'] + "m";
            buildtitle = 'Upgrade to level ' + lvl + '<br> Gold: ' + gld + '<br> Metalum: ' + met + '<br> Tritium: ' + trt + "<br>" + tm;
            console.log(buildtitle);
            build.setAttribute('data-hint', buildtitle);
        } else
        {
            build.setAttribute('data-hint', 'Maximum level reached!!!');
        }
    }
}

function updatebuildingtimes() {
    dorefr = false;

    var tmnow = new Date();
    //console.log(tmnow);
    tmnow = new Date(tmnow + tmdif);
    //console.log('TIME DIF='+tmdif);
    $.each(buildingsArr, function (index, value) {
        if (value['baction'] == 1) {
            tmend = parseInt(value['bactfinished']) * 1000;//TODO:fix this after fixing the previous TODO
            console.log(tmnow);
            console.log(tmend);
            tmstr = '▲ ' + getunixdate(tmend - tmnow);
            if (tmend <= tmnow) {
                dorefr = true;
            }
            createTextLabel(tmstr, 'planetdiv', 'blbl_' + value['btid']);
        }
    });

    if (dorefr) {
        //alert('refresh')
        //window.clearInterval(refid);
        //refid=null;
        refreshData();
    }

    return dorefr;
}

var refid = null;
var refintrval = 1000;
function setAllBuildingInfo() {

    $.each(buildingsArr, function (index, value) {
        setBuildingInfo(value);
    });
    if (refid != null)
        window.clearInterval(refid);
    refid = setInterval(function () {
        updatebuildingtimes();
    }
    , refintrval);

    createTextLabel("", 'actiondiv', 'buildinfo', 300, 5, 300, 130);
}



function doafterload() {

    showBuildings();
    showActionButtons();

    setEvents();

    setAllBuildingInfo();
}


var intid = 0;
function whenload() {

    if (!loaded) {
        return 0;
    }
    clearInterval(intid);

    doafterload();
}

function showPlanet() {

    putdesign();

    n = 0;
    if (!loaded)
        intid = setInterval(whenload, 30)
    else
        doafterload();
}


function getPlanetData(fullrefresh) {

    if ((loaded && (typeof ssx != 'undefined')) && !fullrefresh)
        obj = getAjaxInfo(30, 'planet', 'p=0', 'mydata');
    else
        obj = getAjaxInfo(30, 'planet', 'p=0', 'mydata');
    showPlanet();

}

function refreshData() {
    getPlanetData(true);
    refreshPlanetInfo();
}

function refreshTab() {
    if (mainpage == 'planet') {
//   console.log('seltab='+selectedTab);
        tabpressed(null, selectedTab);

    }

}