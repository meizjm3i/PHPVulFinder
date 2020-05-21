<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/5/19
 * Time: 12:40 AM
 */
/*
 *
 * Sink点
 *
 */
class Sink{
    public $type;    // Sink点类型
    public $name;    // Sink点函数名
    public $args;    // Sink函数参数
    public $linenum; // Sink函数位置
    public function __construct()
    {
        $this->args = array();
    }
}