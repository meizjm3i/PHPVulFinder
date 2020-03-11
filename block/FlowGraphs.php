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
    public $cfg;
    public $basicblock;


    /*
     * 确定首指令：
     * 1. 四元组序列的第一个四元组是一个首指令
     * 2. 任意一个条件或无条件转移指令的目标指令是一个首指令
     * 3. 紧跟在一个条件或无条件转移指令之后的指令是一个首指令
     *
     * 关于函数调用：
     * 每个函数都作为一个新的BasicBlock
     *
     * 确定尾指令：
     * 1. 以下一块开头结尾
     * 2. 以__halt_compiler结尾
     * 3. 以exit、die、return结尾
     * 4. 代码结束
     * 5. 转移语句
     *
     * 确定CFG，考虑在入边和出边存在复数情况下的生成
     *
     */
    public function __construct()
    {
        $this->graph_id = 0;
    }

    public function GenerateCFG(){

    }

    public function BlockDivide($quads){
        $this->graph[$this->graph_id] = new BasicBlock();
        $this->graph[$this->graph_id]->entry = 1;
        $this->graph[$this->graph_id]->inedge = $quads[1];

        $quad_id = count($quads);
        $defined_functions = get_defined_functions();
        $internal_functions = $defined_functions["internal"];
        $user_functions = $defined_functions["user"];
        for($i = 0 ; $i < $quad_id ; $i++){
            if($quads[$i]->op == "JUMP" || $quads[$i]->op == "Expr_FuncCall") {
                /*
                 * 跳转语句或者函数调用语句都可新建一个基本块
                 * 当新建一个基本块时，要先对上一个基本块的outedge进行处理
                 */
                if(!in_array($quads[$i]->arg1->parts[0],$internal_functions)){
                    $this->graph[$this->graph_id]->outedge = $quads[$i];
                    $this->graph_id += 1;
                    $this->graph[$this->graph_id] = new BasicBlock();
                    $this->graph[$this->graph_id]->inedge = $quads[$i+1];
                    $id = $quads[$i]->result;
                    if($quads[$id] != null ){
                        $this->graph_id += 1;
                        $this->graph[$this->graph_id] = new BasicBlock();
                        $this->graph[$this->graph_id]->inedge = $quads[$id];
                    }
                }
            }elseif($quads[$i]->op == "Expr_Exit" || $quads[$i]->op == "Exit_Return" || $quads[$i]->op == "Exit_Die"){
                /*
                 * 以exit、die、return结尾
                 */
                $this->graph[$this->graph_id-1]->outedge = $quads[$i+1];
            }elseif(""){

            }elseif(""){

            }else{
                if($this->graph_id > 1) {
                    $this->graph[$this->graph_id - 1]->outedge = $quads[$i - 1];
                }
            }

        }
    }

    public function optimize($quads){
        for($i=0;$i<count($this->graph);$i++){
            $start = $this->graph[$i]->entry;
            $end   = $this->graph[$i+1]->entry;
            if($end == null){
                $end = count($quads)  ;
            }
            $offset = $end - $start ;
            $code = array_slice($quads,$start,$offset);

            $code = $this->variable_to_id($code);
            // 删除公共子表达式
            $quads = $this->delete_CommonExpr($code,$quads);
            // 删除无用代码
//            $this->delete_UnusedCode($code,$quads);
            return $quads;


        }
    }

    public function unique(){

    }

    public function variable_to_id($code){
        $variable_info = array();
        /*
         * 收集当前Block的变量信息
         */
        for ($i = 0; $i < count($code); $i++){
            if($code[$i]->result instanceof PhpParser\Node\Expr\Variable
            && $code[$i]->op == "Expr_Assign"
            ){
                $variable_info[$code[$i]->result->name] = $code[$i]->id;
            }
        }

        for ($i = 0; $i < count($code); $i++){
            if($code[$i]->arg1 instanceof PhpParser\Node\Expr\Variable){
                if(array_key_exists($code[$i]->arg1->name,$variable_info)){
                    $code[$i]->arg1 = $variable_info[$code[$i]->arg1->name];
                }
            }
        }
        return $code;
    }

    public function delete_CommonExpr($code,$quads){
        $CommonExpr = [];
        foreach ($code as $quad){
            if($quad->op == "Expr_Assign"){
                $id = $quad->id;
                $val = $quad->arg2->value;
                $CommonExpr[$id] = $val;
            }
        }
        $dup = $this->get_keys_for_duplicate_values($CommonExpr);
        echo '**--**';
//        var_dump($dup);
        echo '**--**';

        /*
         * 对于公共子表达式，首先将后续所有的调用指向第一次出现的位置，再删除非第一次的赋值
         */
        foreach ($dup as $k=>$v){
            for($i = 1 ; $i < count($v) ; $i ++){
                $id = $v[$i];
                for($j=0;$j<count($code);$j++){
                    $quad_id = $code[$j]->id;
                    if($code[$j]->arg1 == $id) {
                        $quads[$quad_id]->arg1 = $v[0];
                    }elseif($code[$j]->arg2 == $id){
                        $quads[$quad_id]->arg2 = $v[0];
                    }
                }
                unset($quads[$id]);
            }
        }
        /*
         * 1. 找到所有指向公共子表达式的arg，转指向表达式第一次出现的地方
         *
         * 2. 删除重复子表达式quad的出现
         */
        return $quads;

    }

    /*
     * 在同一个BasicBlock中进行无用代码优化
     */
    public function delete_UnusedCode($BlockId){

    }

    public function get_keys_for_duplicate_values($my_arr) {
        $dup = $new_arr = array();
        foreach ($my_arr as $key => $val) {
            if(!isset($new_arr[$val])){
                $new_arr[$val] = $key;
            }else{
                if(!is_array($dup[$val])){
                    $dup[$val] = array();
                }
                array_push($dup[$val],$new_arr[$val]);
                array_push($dup[$val],$key);
            }
        }
        return $dup;
    }

}