<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/11/12
 * Time: 1:27 AM
 */


class func_table{
    public $func_name;
    public $func_stmt; // statement
    public $in_class; // bool
    public $class_name;
    public $namespace;

    public $func_params;
    public $func_params_count;

    public function __construct($func_name=null,$func_stmt = null,$func_params=null,$func_params_count=null,$in_class = null,$class_name = null,$namespace = null)
    {
        $this->func_name = $func_name;
        $this->func_stmt  = $func_stmt;
        $this->func_params = $func_params;
        $this->func_params_count = $func_params_count;
        $this->in_class   = $in_class;
        $this->class_name = $class_name;
        $this->namespace  = $namespace;
    }
}