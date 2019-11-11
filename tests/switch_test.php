<?php



switch ($a="options"){
    case 'opti'.'o'.'n_1':
        $a = 1;
        break;
    case 'options':
        $a = 2;
        continue;
    case 'option_3':
        $a = 3;
        break;
    default:
        $a = 4;
        break;
}
echo $a;
$a = 'meizj';
