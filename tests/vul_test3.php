<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/5/21
 * Time: 11:12 PM
 */
$a = $_GET['a'];

$b = escapeshellcmd($a);

system($b);