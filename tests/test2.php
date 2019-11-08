<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/10/30
 * Time: 10:03 PM
 */

class test{

    public $command;

    public function __construct($command){
        $this->command = $command;
    }

    public function exec(){
        system($this->command);
    }
}

$a = new test($_GET['a']);