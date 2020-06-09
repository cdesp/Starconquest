<?php

    include "common.php";
    include "shiputils.php";
    db_connect();
    session_start();
    

  $inpupg=filter_input(INPUT_GET, 'upg');
  if (myisset($inpupg)) {
      $upg=$inpupg;
      $id=filter_input(INPUT_GET, 'id');
      switch ($upg) {
          case 'hull':
            $qr="select * from `x_hulls` where `xhullid`=$id LIMIT 1";
            break;
          case 'comp':
            $qr="select * from `x_computers` where `xcompid`=$id LIMIT 1";
            break;
          case 'prop':
            $qr="select * from `x_propulsions` where `xpropid`=$id LIMIT 1";
            break;
          case 'sensor':
            $qr="select * from `x_sensors` where `xsensid`=$id LIMIT 1";
            break;
          case 'shield':
            $qr="select * from `x_shields` where `xshieldid`=$id LIMIT 1";
            break;
          case 'weapon1':
          case 'weapon2':
          case 'weapon3':
            $qr="select * from `x_weapons` where `xweaponid`=$id LIMIT 1";
          break;
          
      }
  
      $inpspeed=filter_input(INPUT_GET, 'speed');
      if (executequery($qr, $qres, $qrcnt) and $qrcnt>0) {
          $qrarr=query_fetch_array($qres);
          $retarr=$qrarr;
        
          //$retarr['size']=650;
          //$retarr['gold']=450;
          //$retarr['metalum']=550;
          //$retarr['tritium']=750;
          switch ($upg) {
            case 'prop':
                  $retarr['speed']=-1;
            break;
        
        
        }
      }
      
      $retarr['upg']=$upg;
      $retarr['id']=$id;
      
      
      echo json_encode($retarr);
  } elseif (myisset($inpspeed)) {
      $size=  filter_input(INPUT_GET, 'size');
      $power= filter_input(INPUT_GET, 'power');
      $retarr['speed']=calculatespeed($power, $size);
      
      echo json_encode($retarr);
  }
