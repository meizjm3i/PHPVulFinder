<?php

function a($s){
    $b = $s."...";
    $c = "ss";
    $d = array($b,$c);
}

function b($ss){
    echo $ss;
    system($ss);
}

$t = "123";
a($t);

$d = "sss";
$e = 123;
b("ls");
