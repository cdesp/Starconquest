<style type="text/css">
.regmain {
	position: relative;
    top: 4px;
    left: 8px;	
    height: 768px;
}
.regbackg {
	position: absolute;
    display: block;
	z-index: -2;
    top: 0px;
    left: 0px;		
}
.regsqr
{
    position: absolute;
    display: block;
    z-index: -1;
    top: 350px;
    left: 250px;	
}
.regdata
{
    position: absolute;
    display: block;
    z-index: 0;
    top: 350px;
    left: 250px;	
}
.textedit{
    font-size:16px;
    width:183px;
}
.textcombo{
    font-size:16px;
    width:52px;
}
.lang{
    position: absolute;
    display: block;
    z-index: 0;
    top: 24px;
    left: 220px;	
    width:80px;	        
}
.uname{
    position: absolute;
    display: block;
    z-index: 0;
    top: 67px;
    left: 220px;	
    width:180px;	    
}
.pass{
    position: absolute;
    display: block;
    z-index: 0;
    top: 111px;
    left: 220px;	
    width:180px;	        
}
.email{
    position: absolute;
    display: block;
    z-index: 0;
    top: 155px;
    left: 220px;	
    width:180px;	        
}
.reg{
    position: absolute;
    display: block;
    z-index: 0;
    top: 194px;
    left: 185px;	    
}
</style>

<?php
if (myisset($secret)) {
    $body="
	
    	<div class='regmain'>
		     <img class='regbackg' src='Images/LogBack.png'>
			 <img class='regsqr' src='Images/LogReg.png'>

  	    <div class='regdata'>
		<form action='index.php' method='post' name='register' onSubmit=''>

                                               <input type='hidden' name='action' value='register'>
                                               <div class='lang'>
                                                <select class='textcombo' name='lang'>";

    $dir = './language';
    $handle = opendir($dir);
    // Lettura...
    while (false !== ($files = readdir($handle))) {
        // Escludo gli elementi '.' e '..' e stampo il nome del file...
        if ($files != '.' && $files != '..' && $files[0] != '_') {
            $body.= '<option selected>'.substr($files, 0, -4).'</option>';
        }
    }

    $body.="</select></div>
                                                <div class='uname'>
                                                    <input class='textedit' type='text' name='user' id='user'>
                                                </div>
                                                <div class='pass textedit'>
                                                    <input class='textedit' type='password' name='pass' id='pass'>                                                                                                
                                                </div>
                                                <div class='email textedit'>
                                                <input class='textedit' type='email' name='email' id='email'>
                                                </div>
                                                <div class='reg'>
                                                 <input type='image' name='reg' id='reg' value='".$lang['register']."' src='/Images/Register.png' />
                                                </div>     
												
                </form>
            </div>
        </div>				
    ";
}
?>