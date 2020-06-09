<?php

    include_once "common.php";
    include_once "myutils.php";
    
        

    function newsysmessage($messg, $category=null, $touserid=null, $tm=null)
    {
        if (!myisset($touserid) and myisset(getsessionvar('id'))) {
            $touserid=$_SESSION['id'];
        }
          
        $mid=gettablenextid('messages', 'msgid');
          
        if ($tm==null) {
            $tm=mtimetn();
        }
        $qr="insert into messages (msgid,msgfrom,msgto,msgtime,msg,msgread,category) values($mid,0,$touserid,$tm,'$messg','N','$category')";
        if (!query_exec($qr)) {
            adddebug('Error inserting system message in message');
            addbottomoutput($qr);
        }
    }
    
    
    function newmessage($messg, $touserid, $fromuserid=null)
    {
        if (!myisset($fromuserid)) {
            if (myisset(getsessionvar('id'))) {
                $fromuserid=$_SESSION['id'];
            }
        }
          
        $mid=gettablenextid('messages', 'msgid');
        $tm=null;//time is set by db
        $qr="insert into messages (msgid,msgfrom,msgto,msgtime,msg,msgread) values($mid,$fromuserid,$touserid,$tm,'$messg','N')";
        if (!query_exec($qr)) {
            adddebug('Error inserting user message in message');
            addbottomoutput($qr);
        }
    }


    function getusermessages(&$qrcnt, $uid=null, $lastmid=null)
    {
        $userid=$_SESSION['id'];
        
        if (!myisset($uid)) {
            $uid=$userid;
        }
          
        if (myisset($lastmid)) {
            $criter=" and `msgid`<$lastmid";
        } else {
            $criter="";
        }
          
        
        $qr="
		   select msgid,msgfrom,msgto,msgtime,u1.name as fromname,u2.name as toname, msg,msgread,`category` from messages  left join users u1 on u1.id=msgfrom inner join users u2 on u2.id=msgto  where msgto=$uid $criter order by msgtime DESC LIMIT 60";
        addbottomoutput($qr);
        if (executequery($qr, $qres, $qrcnt) and $qrcnt>0) {
            return $qres;
        } else {
            return false;
        }
    }
