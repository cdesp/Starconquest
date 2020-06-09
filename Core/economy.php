<?php

    function getupgres($res, $nxtlvl)
    {
        //return 10*$nxtlvl*$res+10*pow(10,$nxtlvl);
        return round(pow($nxtlvl, $nxtlvl/3)*$res);
    }

    function getupgresneeded(&$goldupg, &$metalupg, &$tritiumupg, $nxtlvl)
    {
        $goldupg=getupgres($goldupg, $nxtlvl);
        $metalupg=getupgres($metalupg, $nxtlvl);
        $tritiumupg=getupgres($tritiumupg, $nxtlvl);
    }
    
    function calcplanetproduction($pid, $resname, $res)
    {
        $quer = "SELECT * FROM `buildings`,`buildingtypes` WHERE `pid`=$pid  and `buildings`.`btid`=`buildingtypes`.`btid` and (`reseffect`='all' or `reseffect` like '%$resname%' ) order by btype" ;
        if (executequery($quer, $qres, $qrcnt)) {
            for ($i=1;$i<$qrcnt+1;$i++) {
                $dbarr=query_fetch_array($qres);
                $p=$dbarr['resefperc'];//how it affects as percantage of basiC prod
                $blevel=$dbarr['blevel']-1;
                $res+=floor($res*$blevel*$p/100);
            }
        }
        
        return $res;
    }
