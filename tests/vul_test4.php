<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/5/21
 * Time: 11:57 PM
 */

function demo($d){
    return $d;
}
$a = $_GET['a'];
$b = demo($a);
system($b);