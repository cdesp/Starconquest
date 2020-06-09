// JavaScript Document

var bgoffsetx, bgquadrh, quadsizex;
var bgoffsety, bgquadrv, quadsizey;


function imagestring(canv, sz, x, y, txt, col) {
    ctx2d = canv.getContext("2d");

//	ctx2d.lineWidth=1;
    ctx2d.font = sz + "pt Arial";
    //console.log(x+":"+y+" "+txt);

    ctx2d.fillStyle = col;
    ctx2d.fillText(txt, x, y);
//	ctx2d.strokeStyle=col;
//	ctx2d.strokeText(txt, x, y);
    ctx2d.stroke;
}



function createGrid(c, totalW, totalH, blockW, blockH, offsX, offsY, col, clear) {

    var mapGridCanvas = c.getContext("2d");
    if (clear)
        mapGridCanvas.clearRect(0, 0, c.width, c.height);
    mapGridCanvas.globalAlpha = 1;
    mapGridCanvas.strokeStyle = col;
    mapGridCanvas.lineWidth = 1;
    mapGridCanvas.beginPath();
    var x = 0;
    var y = 0;
    for (var i = 0; i <= Math.round(totalW / blockW); i++) {

        x = offsX + i * blockW;
        y = offsY + 0;
        mapGridCanvas.moveTo(x, y);
        mapGridCanvas.lineTo(x, y + totalH);

    }

    for (var j = 0; j <= Math.round(totalH / blockH); j++) {

        x = offsX + 0;
        y = offsY + j * blockH;

        mapGridCanvas.moveTo(x, y);
        mapGridCanvas.lineTo(x + totalW, y);

    }

    mapGridCanvas.stroke();
}


function setDash(context, array, offset) {

    offset = (typeof offset === 'number') ? offset : 0;


    if (typeof context.mozDash !== 'undefined') { //Firefox
        console.log('mozilla');
        console.log(array);
        context.mozDash = array;
        context.mozDashOffset = offset;

    } else
    if (typeof context.setLineDash !== 'undefined') { //Firefox { //Chrome
        // console.log('Chrome');
        context.setLineDash(array);
        context.lineDashOffset = offset;
    }
}

function getCanvas(canvname) {
    return	document.getElementById(canvname);
}

function loadCanvas(id, canvname, cw, ch) {
    div = document.getElementById(id);
    if (div == null)
        div = getElementByName(id);
    if (cw == 0)
        cw = div.scrollWidth;
    if (ch == 0)
        ch = div.scrollHeight;


    canvas = document.getElementById(canvname);
    if (canvas == null) {
        console.log('Create canvas ' + canvname);
        canvas = document.createElement('canvas');
        canvas.id = canvname;
        canvas.width = cw;
        canvas.height = ch;
        canvas.style.zIndex = 1;
        canvas.style.position = "absolute";
//        canvas.id     = "CursorLayer";
        //  canvas.style.border   = "1px solid";



        div.appendChild(canvas);
    }

    return canvas;
}


function drawCircle(x, y, distx, disty, col, canvname, id) {
    canvas = loadCanvas(id, canvname);
    var context = canvas.getContext("2d");


    if (distx == disty) {
        context.beginPath();
        context.arc(x, y, distx, 0, 2 * Math.PI, false);
    } else {
        context.save(); // save state
        context.beginPath();

        context.translate(x - distx, y - disty);
        context.scale(distx, disty);
        context.arc(1, 1, 1, 0, 2 * Math.PI, false);

        context.restore(); // restore to original state
    }

    // context.fillStyle = "black";
    // context.fill();
    //context.setLineDash([2,3]);
    setDash(context, [2, 3], 0);
    context.lineWidth = 2;
    context.strokeStyle = col;
    context.stroke();
}


function clearCanvas(canvname) {
    canvas = document.getElementById(canvname);
    if (canvas == null)
        return 0;
    var context = canvas.getContext("2d");
    context.clearRect(0, 0, canvas.width, canvas.height)
}

function paintarrow(context, fromx, fromy, tox, toy, hdlen) {
    if (typeof hdlen === 'undefined')
        hdlen = 10;
    var headlen = hdlen;	// length of head in pixels
    var dx = tox - fromx;
    var dy = toy - fromy;
    var angle = Math.atan2(dy, dx);
//		context.moveTo(fromx, fromy);
//		context.lineTo(tox, toy);
    context.lineTo(tox - headlen * Math.cos(angle - Math.PI / 6), toy - headlen * Math.sin(angle - Math.PI / 6));
    context.moveTo(tox, toy);
    context.lineTo(tox - headlen * Math.cos(angle + Math.PI / 6), toy - headlen * Math.sin(angle + Math.PI / 6));
}

//realxy coords
function paintline(frmx, frmy, tox, toy, canvname, id, col, arrow, lined, lw, hdlen) {
    if (typeof lw === 'undefined')
        lw = 2;
    c = loadCanvas(id, canvname);
    var ctx = c.getContext("2d");
    ctx.beginPath();
    ctx.moveTo(frmx, frmy);
    ctx.lineTo(tox, toy);
    setDash(ctx, lined, 0);

    if (arrow) {
        paintarrow(ctx, frmx, frmy, tox, toy, hdlen);
        //ctx.setLineDash([0]);
    }


    ctx.lineWidth = lw;
    ctx.strokeStyle = col;
    ctx.stroke();

}




//simple line algorithm
function addroutepoint(arr, x, y) {
    if (arr != null)
        i = arr.length;
    else
        return false;
    arr[i] = new Array;
    arr[i]['x'] = x;
    arr[i]['y'] = y;

// console.log(i+". x,y="+x+","+y);

}


function swap(a, b) {//a and b must be objects
//  console.log("in swap");
//  console.log(a.v+"<-->"+b.v);

    t = a.v;
    a.v = b.v;
    b.v = t;


//  console.log(a.v+"<-->"+b.v);
//  console.log("out swap");  
}

function get_line(x1, y1, x2, y2) {
    //console.log("from "+x1+","+y1+"<br>");
    //console.log("to   "+x2+","+y2+"<br>");
    x1 = {v: x1};
    y1 = {v: y1};
    x2 = {v: x2};
    y2 = {v: y2};

    points = new Array;
    issteep = Math.abs(y2.v - y1.v) > Math.abs(x2.v - x1.v);
    if (issteep) {
        swap(x1, y1);
        swap(x2, y2);
    }
    rev = false;
    if (x1.v > x2.v) {
        swap(x1, x2);
        swap(y1, y2);
        rev = true;
    }
    deltax = x2.v - x1.v;
    deltay = Math.abs(y2.v - y1.v);
    error = Math.floor(deltax / 2);
    y = y1.v;
//    $ystep = NULL;
    if (y1.v < y2.v)
        ystep = 1;
    else
        ystep = -1;
    console.log(x1.v + " to " + x2.v);
    for (x = x1.v; x < x2.v + 1; x++) {
        if (issteep)
            addroutepoint(points, y, x);
        else
            addroutepoint(points, x, y);
        error -= deltay;
        if (error < 0) {
            y += ystep;
            error += deltax;
        }
    }
    // Reverse the list if the coordinates were reversed
    if (rev && (points != null))
        points.reverse();

    return points;
}
