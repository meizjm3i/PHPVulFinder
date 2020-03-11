<?php

function a($s){
    $b = $s."...";
    $c = "ss";
    die();
    $d = array($b,$c);
}

function b($ss){
    echo $ss;
    system($ss);
    return "success";
}

$t = "123";
a($t);

$d = "sss";
$e = 123;
$f = b("ls");
