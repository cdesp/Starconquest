<?php

require_once "myutils.php";

session_start();

if (myisset(filter_input(INPUT_GET, 'sesvar'))) {
    $sesvar = filter_input(INPUT_GET, 'sesvar');
    $sesval = filter_input(INPUT_GET, $sesvar);
    $_SESSION[$sesvar] = $sesval;
    echo $sesvar . '=' . $sesval;
}
