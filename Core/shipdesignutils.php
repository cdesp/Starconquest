<?php

function getSelectedShipHull($shiptpid)
{
    $qur = "SELECT * FROM `shiptypes`,`x_hulls` where (stid=$shiptpid) and (hullid=xhullid) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

//TODO:get only discovered in tech hulls not all
function getAllShipHullsforUser($uid, &$qrcnt)
{

    //this query get all hulls that the user has discovered
    $qur = "SELECT * FROM `x_hulls` order by `size`";
//    $qur="SELECT * from x_hulls where techidneeded in
//    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

function getAllShipComputersforUser($uid, &$qrcnt)
{

    //this query get all computers that the user has discovered
    $qur = "SELECT * FROM `x_computers` order by `size`";
//    $qur="SELECT * from x_computers where techidneeded in
//    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

function getAllShipPropulsionsforUser($uid, &$qrcnt)
{

    //this query get all propulsions that the user has discovered
    $qur = "SELECT * FROM `x_propulsions`  order by `size`";
//    $qur="SELECT * from x_propulsions where techidneeded in
//    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

function getAllShipSensorsforUser($uid, &$qrcnt)
{

    //this query get all sensors that the user has discovered
    $qur = "SELECT * FROM `x_sensors`  order by `size`";
//    $qur="SELECT * from x_sensors where techidneeded in
//    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

function getAllShipShieldsforUser($uid, &$qrcnt)
{

    //this query get all Shields that the user has discovered
    $qur = "SELECT * FROM `x_shields`  order by `size`";
//    $qur="SELECT * from x_shields where techidneeded in
//    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";

    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}

function getAllShipWeaponsforUser($uid, &$qrcnt)
{

    //this query get all weapons that the user has discovered
    $qur = "SELECT * from x_weapons where techidneeded in  
    					(select techid from `techuser` where userid=$uid and buildtime=0) order by `size`";
    if (executequery($qur, $qres, $qrcnt) and $qrcnt > 0) {
        $qres = query_exec($qur);
        //addbottomoutput($qur);
        return $qres;
    } else {
        return null;
    }
}
