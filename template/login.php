<style type="text/css">
.logmain {
	position: relative;
    top: 4px;
    left: 8px;	
	height: 768px;	
        width:1024px;
}
.logbackg {
	position: absolute;
    display: block;
	z-index: -2;
    top: 0px;
    left: 0px;		
}
.logsqr
{
    position: relative;
    display: block;
    z-index: -1;
    top: 350px;
    left: 250px;	
}
.logdata
{
    position: absolute;
    display: block;
    z-index: 0;
    top: 400px;
    left: 300px;	
}
.uname{
    position: absolute;
    display: block;
    z-index: 0;
    top: 16px;
    left: 170px;	
    width:180px;	    
}
.pass{
    position: absolute;
    display: block;
    z-index: 0;
    top: 60px;
    left: 170px;	
    width:180px;	        
}
.reg{
    position: absolute;
    display: block;
    z-index: 0;
    top: 120px;
    left: 225px;
    width:140px;
    height:35px;
    cursor: pointer; 
    background-image:url('/Images/Register.png');
}
.reg:hover{
   background-image:url('/Images/Register_ovr.png');
}
.log{
    position: absolute;
    display: block;
    z-index: 0;
    top: 120px;
    left: 30px;	    
    width:140px;
    height:35px;    
    cursor: pointer; 
    background-image:url('/Images/Login.png');
}
.log:hover{
   background-image:url('/Images/Login_ovr.png');
}


</style>
<?php
if (myisset($secret)) {
    $body="
	<div class='logmain'>
                <img class='logbackg' src='Images/LogBack.png'>
		<img class='logsqr' src='Images/LogSqr.png'>
                <div class='logdata'>
		    <form name='login' method='post' action=''>
				 
                	<input type='hidden' name='action' value='login'>
                	
                        <div class='uname'>    
                            <input style='font-size:16px;width:183px;' type='text' name='user' id='user'>
                        </div>
                        <div class='pass'>    
                            <input style='font-size:16px;width:183px;' type='password' name='pass' id='pass'>			                      
                        </div>

                           
                        <div class='log' name='login' alt='Login' id='login' onClick='javascript:document.forms[0].submit();'>
                          
                        </div>    

                        <a href='?pg=register'>   
                        <div class='reg' name='register' alt='Register' id='register'>
                          
                        </div> </a>   
                        
                        <input type='submit' hidden />
                                                    
                    </form>
                
		</div>		
	</div>
   ";
}

?>