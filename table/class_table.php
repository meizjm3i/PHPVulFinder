<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/2/17
 * Time: 10:33 AM
 */

class class_table{
    public $class_name;
    public $in_namespace;
    public $extends;
    public $member_var;

    public function __construct($class_name,$in_namespace,$extends,$member_var)
    {
        $this->class_name = $class_name;
        $this->in_namespace = $in_namespace;
        $this->extends = $extends;
        $this->member_var = $member_var;
    }
}