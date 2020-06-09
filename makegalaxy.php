<?php
  require 'Core/galaxyutils.php';
  initgalaxy();
  if (myisset($_REQUEST['submit'])) {
      $solno = filter_input(INPUT_GET, 'solsysno');
      $solsize = filter_input(INPUT_GET, 'solsize');
      $solcov = filter_input(INPUT_GET, 'plancover');

      
      creategalaxymap($solno, $solsize, $solcov);
      die('Galaxy Created!!!') ;
  }

?>
<form name="frmCreateGalaxy" method="get" action=""><label>Number of solar systems :</label><input name="solsysno" type="text" value="20" size="10" maxlength="2"><br><br>
<label>Solar system size :</label><input name="solsize" type="text" value="10" size="10" maxlength="2"><br><br>
<label>Solar system coverage % :</label><input name="plancover" type="text" value="7" size="10" maxlength="2"><br><br>
<input name="submit" type="submit" value="Create Galaxy">

</form>
