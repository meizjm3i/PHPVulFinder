<?php


$a = "evil";
$b = "ls";
switch ($a){
    case 'evil':
        evil($b);
        break;
    case 'not_evil':
        print_r($b);
        break;
    default:
        echo "no evil called.";
        break;
}

function evil($command){
    system($command);
}
