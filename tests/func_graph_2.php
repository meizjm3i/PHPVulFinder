<?php

function a($s){
    $b = $s."...";
    $c = "ss";
    $d = array($b,$c);
}

function b($ss){
    echo $ss;
    $a = "asd";
    $b = "dfg";
    system($b);
    $para = $_GET['w'];
    $c = "ppp";
    $cd = "qwe";
    system($para);
    return "success";
}

$t = "123";
a($t);

$d = "sss";
$e = $_GET['p'];
$f = b($e);

