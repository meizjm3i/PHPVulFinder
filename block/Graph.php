<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2020/5/3
 * Time: 6:18 AM
 */
class CFGEdge{
    public $false=0;
    public $true=1;
    public $normal=2;
    public $no_edge=3;

    public $type;
    public $from;
    public $dest;

}
class CFGNode{
    public $inedge;
    public $outedge;
    public $ParseNode;
    public function __construct()
    {
        $this->inedge = array();
        $this->outedge = array();
    }
}
class Graph{
    public $head;
    public $tail; // 控制流图的最后一个Node
    public function __construct()
    {
        $this->head = new CFGNode();
        $this->tail = new CFGNode();
    }
}

class Variable{
    public $BasicBlockId;
    public $Varname;
    public $linenum;
    public $from;
    public $tainted;
}
