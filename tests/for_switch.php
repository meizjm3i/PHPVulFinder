<?php

$a = 0;
for($i=0;$i<10;$i++){
    switch($i){
        case '4':
            $a = 2;
            break;
        case '5':
            $a = 3;
            continue 2;
        case '6':
            $a += 1;
            continue;
        default:
            break;
    }

    if($a == 3){
        $a += 1;
    }
}

echo $a;
