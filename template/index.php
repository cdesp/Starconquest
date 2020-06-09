<html lang='en'>
<?php
require_once(MYPATH.'/Core/myutils.php');
global $debugdata,$body,$bodystyle,$head,$topoutput,$bottomoutput;



if (myisset($secret)) {
    $ingame= myisset(getsessionvar('id')) == true ;
   
    if ($ingame) {//general info
        includecorepage("planetinfo");
        addoutput(" <div class='planetinfo' id='planetinfo'>", '');
        showplanetinfo($ajax, false);
        addoutput("</div>", '');
    }
    addalltherest();//adds page code,page style,javascript and included files;
    $tmnow=mtimetn();
    echo "<head>".'

    

<meta charset="UTF-8">
   '."
   <title>"."STAR CONQUEST"."</title>
   <link rel='shortcut icon' href='favicon.ico' />
   
   <link rel='stylesheet' href='jscript/JQuery/jquery-ui.css' />
   <link rel='stylesheet' href='jscript/Selecter-master/jquery.fs.selecter.css' type='text/css' media='all'/>
   <link type='text/css' rel='stylesheet' href='jscript/tooltip/style.css' />
   <link type='text/css' rel='stylesheet' href='template/defstyle.css' />

   <script type='text/javascript' src='jscript/tooltip/script.js'></script>   
   <script src='jscript/JQuery/jquery-1.12.4.js'></script>
   <script src='jscript/JQuery/jquery-ui.js'></script>
   <script src='jscript/astar/graph.js'></script>   
   <script src='jscript/astar/astar.js'></script>
   <script src='jscript/graphics.js'></script>

   <script src='jscript/NumberFormat154.js'></script>      

   <script src='jscript/jquery.scrollintoview.min.js'></script>
	

   <script type='text/javascript'> servertime=$tmnow;</script>      
   $head
   ";
    //include_once "defstyle.css";
   
   
  
   
    echo '<style type="text/css">'.$bodystyle.'</style>';
    echo " 
	</head>
	<body onload='myload()'>
		 
	";
    
    echo $topoutput;
    //output debug
    echo "<DIV class='debug' >";
    echo "<br>---<mark>DEBUG DATA START</mark>---<br>";
    echo $debugdata;
    echo "<br>---<mark>AJAX DEBUG</mark>---<br>";
    echo "<DIV id='debug' >";

    echo "</div>";
    echo "<br>---DEBUG DATA END---<br>";
    echo "</DIV>";


    if (!$ingame) {
        if ($pg != "register") {
            require('template/login.php');
        } else {
            require('template/register.php');
        }
        // echo $body;
    } else {
//     <a href='index.php?pg=none' ><img class='menu1' src='Images/Menu_none.jpg'> </a>
//     <a href='index.php?pg=none' ><img class='menu2' src='Images/Menu_none.jpg'> </a>
//     <a href='index.php?pg=none' ><img class='menu3' src='Images/Menu_none.jpg'> </a>
        // <a href='index.php?pg=map'><img class='menu4' src='Images/Menu_Map.png' title='MAP'></a>

        echo "
  <div class='main'>
     <img class='backg' src='Images/SpaceBack.jpg'>
     
     <div class='mytopmenu clickmenu menu4 menuMap'><a href='index.php?pg=map'></a></div>
     <div class='mytopmenu clickmenu menu5 menuBattle'><a href='index.php?pg=battle'></a></div>
     <div class='mytopmenu clickmenu menu6 menuShipdesign'><a href='index.php?pg=shipdesign'></a></div>
     <div class='mytopmenu clickmenu menu7 menuTech'><a href='index.php?pg=tech'></a></div>
     <div class='mytopmenu clickmenu menu8 menuDiplomacy'><a href='index.php?pg=diplomacy'></a></div>
	 ";
    }

    //place the page

    echo $body;


    echo "  </div>
			";
    echo "<br>";
    require('template/footer.php');
    echo "			
		<div id='bottomout' style='font-size:12px;min-height:50px;width:1000px;background-color:#804020'>";
    echo $bottomoutput;
    echo"
		</div>

		</body>
		
		</html>";
}
?>