<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/11/5
 * Time: 11:41 AM
 */


/*
 *
 * 基本块划分，用来存放经过中间代码优化后的结果
 *
 * 基本块的划分结果存放在类 ControlFlowGraph中
 *
 */
class BasicBlock{
    // 出边与入边
    public $inedge;
    public $outedge;

    /*
     * 控制流图中的两个基本块
     *
     * entry: 流图的开始点
     * exit: 流图的结束点
     */

    public $entry;
    public $exit;

    public $blockSummary;

    public function set_entry($entry){
        $this->entry = $entry;
    }

    public function get_entry(){
        return $this->entry;
    }

    public function set_exit($exit){
        $this->exit = $exit;
    }

    public function get_exit(){
        return $this->exit;
    }

    public function set_inedge($inedge){
        $this->inedge = $inedge;
    }
    public function get_inedge(){
        return $this->inedge;
    }

    public function set_outedge($outedge){
        $this->outedge = $outedge;
    }
    public function get_outedge(){
        return $this->outedge;
    }

    public function set_blocksummary($blockSummary){
        $this->blockSummary = $blockSummary;
    }

    public function get_blocksummary(){
        return $this->blockSummary;
    }

}