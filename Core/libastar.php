<?php

// A* algorithm by aaz, found at
// http://althenia.net/svn/stackoverflow/a-star.php?rev=7
// Binary min-heap with element values stored separately
include_once "galaxyutils.php";
include_once "myutils.php";
global $galaxysize,$solsyssize;

function heap_float(&$heap, &$values, $i, $index)
{
    for (; $i; $i = $j) {
        $j = ($i + $i%2)/2 - 1;
        if ($values[$heap[$j]] < $values[$index]) {
            break;
        }
        $heap[$i] = $heap[$j];
    }
    $heap[$i] = $index;
}

function heap_push(&$heap, &$values, $index)
{
    heap_float($heap, $values, count($heap), $index);
}

function heap_raise(&$heap, &$values, $index)
{
    heap_float($heap, $values, array_search($index, $heap), $index);
}

function heap_pop(&$heap, &$values)
{
    $front = $heap[0];
    $index = array_pop($heap);
    $n = count($heap);
    if ($n) {
        for ($i = 0;; $i = $j) {
            $j = $i*2 + 1;
            if ($j >= $n) {
                break;
            }
            if ($j+1 < $n && $values[$heap[$j+1]] < $values[$heap[$j]]) {
                ++$j;
            }
            if ($values[$index] < $values[$heap[$j]]) {
                break;
            }
            $heap[$i] = $heap[$j];
        }
        $heap[$i] = $index;
    }
    return $front;
}


// A-star algorithm:
//   $start, $target - node indexes
//   $neighbors($i)     - map of neighbor index => step cost
//   $heuristic($i, $j) - minimum cost between $i and $j

function a_star($start, $target, $neighbors, $heuristic)
{
    $open_heap = array($start); // binary min-heap of indexes with values in $f
    $open      = array($start => true); // set of indexes
    $closed    = array();               // set of indexes

    $g[$start] = 0;
    $h[$start] = $heuristic($start, $target);
    $f[$start] = $g[$start] + $h[$start];

    while ($open) {
        $i = heap_pop($open_heap, $f);
        unset($open[$i]);
        $closed[$i] = true;

        if ($i == $target) {
            $path = array();
            for (; $i != $start; $i = $from[$i]) {
                $path[] = $i;
            }
            return array_reverse($path);
        }

        foreach ($neighbors($i) as $j => $step) {
            if (!array_key_exists($j, $closed)) {
                if (!array_key_exists($j, $open) || $g[$i] + $step < $g[$j]) {
                    $g[$j] = $g[$i] + $step;
                    //	adddebugval('step',$step);
                    $h[$j] = $heuristic($j, $target);
                    $f[$j] = $g[$j] + $h[$j];
                    $from[$j] = $i;

                    if (!array_key_exists($j, $open)) {
                        $open[$j] = true;
                        heap_push($open_heap, $f, $j);
                    } else {
                        heap_raise($open_heap, $f, $j);
                    }
                }
            }
        }
    }

    return false;
}



//
function node($x, $y)
{
    global $width;
    return $y * $width + $x;
}

function coord($i)
{
    global $width;
    $x = $i % $width;
    $y = ($i - $x) / $width;
    return array($x, $y);
}

function neighbors($i)
{
    global  $width, $height;
    list($x, $y) = coord($i);
    $neighbors = array();
    if ($x-1 >= 0) {
        $neighbors[node($x-1, $y)] = getsolardist($x, $y, $x-1, $y);
    }
    if ($x+1 < $width) {
        $neighbors[node($x+1, $y)] = getsolardist($x, $y, $x+1, $y);
    }
    if ($y-1 >= 0) {
        $neighbors[node($x, $y-1)] = getsolardist($x, $y, $x, $y-1);
    }
    if ($y+1 < $height) {
        $neighbors[node($x, $y+1)] = getsolardist($x, $y, $x, $y+1);
    }

    if ($x-1 >= 0 and $y-1>=0) {
        $neighbors[node($x-1, $y-1)] = getsolardist($x, $y, $x-1, $y-1);
    }
    if ($x+1 < $width and $y+1<$width) {
        $neighbors[node($x+1, $y+1)] = getsolardist($x, $y, $x+1, $y+1);
    }
    if ($x-1 >= 0 and $y+1 < $width) {
        $neighbors[node($x-1, $y+1)] = getsolardist($x, $y, $x-1, $y+1);
    }
    if ($x+1 < $width and $y-1 >=0) {
        $neighbors[node($x+1, $y-1)] = getsolardist($x, $y, $x+1, $y-1);
    }
    
    
    return $neighbors;
}

function getsolardist($x1, $y1, $x2, $y2)
{
    global $solsyssize;
    $solix=floor($x1 / $solsyssize);
    $soliy=floor($y1 / $solsyssize);
    $soljx=floor($x2 / $solsyssize);
    $soljy=floor($y2 / $solsyssize);
    $soldist=(abs($solix - $soljx) + abs($soliy - $soljy))*30;
    //	adddebugval('solix:soliy',$solix.','.$soliy);
    //	adddebugval('soljx:soljy',$soljx.','.$soljy);
    if ($soldist>0) {
        $soldist=floor(sqrt($soldist));
    }
//    adddebugval('soldist',$soldist);
    //	adddebugval('solweight',$soldist*30);
    return 1+$soldist*30;
}

function heuristic($i, $j)
{
    list($i_x, $i_y) = coord($i);
    list($j_x, $j_y) = coord($j);
    //	adddebugval('i_x:i_y',$i_x.','.$i_y);
    //	adddebugval('j_x:j_y',$j_x.','.$j_y);

    
    $dist=(abs($i_x - $j_x) + abs($i_y - $j_y));
    //	adddebugval('dist',$dist);
    $soldist=getsolardist($i_x, $i_y, $j_x, $j_y);


    $retval= $dist + $soldist; //change solar systems

    //	adddebugval('retval',$retval);
    return $retval;
}




function get_starline($x1, $y1, $x2, $y2)
{
    global $width,$height,$galaxysize,$solsyssize;




    $width  = $galaxysize*$solsyssize;
    $height = $galaxysize*$solsyssize;

    $start  = node($x1, $y1);
    $target = node($x2, $y2);

    $path = a_star($start, $target, 'neighbors', 'heuristic');

    array_unshift($path, $start);
    $n=0;
    foreach ($path as $i) {
        list($x, $y) = coord($i);
        $arr[$n]['x']=$x;
        $arr[$n]['y']=$y;
        // adddebugval("$n. x,y",$x.':'.$y);
        $n++;
    }
    return $arr;
}
