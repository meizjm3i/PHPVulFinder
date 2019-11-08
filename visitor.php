<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/10/29
 * Time: 10:34 AM
 */

class NodeInitVisitor extends PhpParser\NodeVisitorAbstract{
    private $nodes = array();
    public function beforeTraverse(array $nodes){
        $this->nodes = $nodes ;
    }
    //getter
    public function getNodes(){
        return $this->nodes ;
    }
}

