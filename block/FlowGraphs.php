<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/11/13
 * Time: 12:18 AM
 */

class FlowGraphs{
    public $graph;
    public $graph_id;

    public $func_graph;
    /*
     * 确定首指令：
     * 1. 四元组序列的第一个四元组是一个首指令
     * 2. 任意一个条件或无条件转移指令的目标指令是一个首指令
     * 3. 紧跟在一个条件或无条件转移指令之后的指令是一个首指令
     *
     * 关于函数调用：
     * 每个函数都作为一个新的graph，当发生函数调用时，原有graph直接接上函数的graph
     *
     *
     */
    public function __construct()
    {
        $this->graph_id = 0;
    }

    public function parseFuncCall($funcs){
        foreach ($funcs as $func){
            $func_name = $func->func_name;
            $ast2ir = new AST2IR();
            $quads = $ast2ir->SimpleParse($func->func_stmt);
        }
    }


    public function BlockDivide($quads,$funcs){

        var_dump($quads);

//        echo "*******";

//        $this->parseFuncCall($funcs);

        $this->graph[$this->graph_id] = new BasicBlock();
        $this->graph[$this->graph_id]->entry = 1;
        $this->graph_id += 1;
        $quad_id = count($quads);

        for($i = 0 ; $i < $quad_id ; $i++){
            if($quads[$i]->op == "JUMP"){
                $this->graph[$this->graph_id] = new BasicBlock();
                $this->graph[$this->graph_id]->entry = $quads[$i+1];
                $this->graph_id += 1;

                $this->graph[$this->graph_id] = new BasicBlock();
                $id = $quads[$i]->result;
                $this->graph[$this->graph_id]->entry = $quads[$id];
                $this->graph_id += 1;

            }
        }

//        var_dump($this->graph);

    }
}