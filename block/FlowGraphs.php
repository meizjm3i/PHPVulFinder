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
    public $edge_info;
    public $quads;
    public $var;
    public $sinks;
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
        $this->quads = $quads;
        $this->graph[$this->graph_id] = new BasicBlock();
        $this->graph[$this->graph_id]->entry = 1;
        $this->graph[$this->graph_id]->inedge = $quads[1];

        $quad_id = count($quads);
        $defined_functions = get_defined_functions();
        $internal_functions = $defined_functions["internal"];
        $user_functions = $defined_functions["user"];
//        var_dump($quads);
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
                    $this->graph[$this->graph_id]->inedge = $quads[$i];
                    $id = $quads[$i]->result;
                    if($quads[$id] != null ){
                        $this->graph_id += 1;
                        $this->graph[$this->graph_id] = new BasicBlock();
                        $this->graph[$this->graph_id]->inedge = $quads[$id];
                    }
                }
            }elseif($quads[$i]->op == "Expr_Exit" || $quads[$i]->op == "Stmt_Return" || $quads[$i]->op == "Exit_Die"){
                /*
                 * 以exit、die、return结尾
                 */
                $this->graph[$this->graph_id-1]->outedge = $quads[$i];
            }elseif($quads[$i]->op == "Expr_FuncCall_End"){
                /*
                 * 每个函数调用结束时都表示一个BasicBlock的完结
                 */
                $this->graph[$this->graph_id-1]->outedge = $quads[$i];
            }elseif($i == $quad_id - 1){
                $this->graph[$this->graph_id]->outedge = $quads[$i];
            }else{
                if($this->graph_id > 1) {
                    $last_graph_outedge_id = $this->graph[$this->graph_id-1]->outedge->id;
                    $this->graph[$this->graph_id]->inedge  = $quads[$last_graph_outedge_id];
                    $this->graph[$this->graph_id]->outedge = $quads[$i];
                }
            }

        }
    }

    public function optimize($quads){
//        var_dump($this->quads);
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

//            $quads = $this->delete_CommonExpr($code,$quads);

            // 删除无用代码
//            $this->delete_UnusedCode($code,$quads);

//            var_dump($quads);

        }

        return $quads;
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

//        var_dump($variable_info);
        for ($i = 0; $i < count($code); $i++){
            if($code[$i]->arg1 instanceof PhpParser\Node\Expr\Variable){
                if(array_key_exists($code[$i]->arg1->name,$variable_info)){
//                    echo "**";
//                    var_dump($code[$i]->arg1->name);
//                    var_dump($variable_info[$code[$i]->arg1->name]);
                    $code[$i]->arg1 = $variable_info[$code[$i]->arg1->name];
                }
            }
        }

//        var_dump($code);
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
        /*
         * 对于公共子表达式，首先将后续所有的调用指向第一次出现的位置，再删除非第一次的赋值
         */

        var_dump($dup);
        foreach ($dup as $k=>$v){
            for($i = 1 ; $i < count($v) ; $i ++){
                $id = $v[$i];
                for($j=0;$j<count($code);$j++){
                    $quad_id = $code[$j]->id;
                    if($code[$j]->arg1 == $id) {
                        echo "***";
                        var_dump($code[$j]);
                        var_dump($v);
                        var_dump($id);
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
    /*
     * CFG生成函数
     * 注意node和edge
     *
     * 一个CFGNode需要通过通过两个Edge形容：来源、去向
     * 来源：使用CFGEdge描述
     * 去向：使用CFGEdge描述
     *
     */
    public function create_graph(){
        $this->cfg = array();
        $this->edge_info = array();

        for($id = 0;$id <= count($this->graph);$id++){
            $cfg = new Graph();
            if($id == 0){
                // CFG的第一个元素没有head，因为首语句就直接进入
                //
                $inedge = new CFGEdge();
                $inedge->from = $this->graph[$id];
                $next_id = $this->findNext($id);
                $inedge->dest = $this->graph[$next_id];
                if(!in_array($this->graph[$id]->outedge->op,array("Stmt_Switch","Stmt_If","Expr_BinaryOp_LogicalOr"))){
                    $inedge->type = $inedge->normal;
                }else {
                    $inedge->type = "";
                }
                array_push($cfg->head->inedge,$inedge);
                $outedge = new CFGEdge();
                $outedge->from = $this->graph[$next_id];
                $outedge->dest = $this->graph[$this->graph[$this->findNext($next_id)]];
                array_push($cfg->head->outedge,$outedge);
                $this->addEdge($inedge);
                $this->addEdge($outedge);
                $node = $this->findTail($next_id);
                $inedge = new CFGEdge();
                $outedge = new CFGEdge();
                $outedge->from = $this->graph[$node[0]];
                $outedge->dest = $this->graph[$node[1]];
                array_push($cfg->tail->inedge,$inedge);
                array_push($cfg->tail->outedge,$outedge);
                array_push($this->cfg,$cfg);
            }

        }
    }


    public function findNext($basic_start){
        for($i=$basic_start;$i<count($this->graph);$i++){
            if($this->graph[$i]->inedge->id == $this->graph[$basic_start]->outedge->id){
                return $i;
            }
        }
        return null;
    }
    public function findEdge($basic_id){
        $edge = new CFGEdge();
        $edge->from = $this->graph[$basic_id];
        $next = $this->findNext($basic_id);
        if($next != null || $basic_id == count($this->graph)-1){
            $edge->dest = $this->graph[$next];
//            var_dump($edge);
            $this->addEdge($edge);
        }
        return $next;
    }
    public function findTail($basic_id){
        $record = array();
        /*
         * record:
         */
        for($i = $basic_id;$i < count($this->graph);$i++){
            $next = $this->findEdge($i);
            if($next == null){
                break;
            }else{
                array_push($record,$next);
            }
        }
        $prev = $record[count($record)-2];
        return array($prev,$next);
    }

    public function extractVar(){
        $this->var = array();
//        var_dump($this->quads);
//        var_dump($this->graph);
        for($i=0;$i<count($this->graph);$i++){

            for($j=$this->graph[$i]->inedge->id;$j<$this->graph[$i]->outedge->id;$j++){

                if($this->quads[$j]->op == "Expr_Assign" && $this->quads[$j]->result instanceof PhpParser\Node\Expr\Variable){
                    $var = new Variable();
                    $var->BasicBlockId = $i;
                    $var->from = array($this->quads[$j]->arg1,$this->quads[$j]->arg2);
                    $var->linenum = array($this->quads[$j]->result->getStartLine(),$this->quads[$j]->result->getEndLine());
                    $var->Varname = $this->quads[$j]->result->name;
                    $this->addVar($var);
                }
                if($this->quads[$j]->op == "Param" && $this->quads[$j]->result instanceof PhpParser\Node\Expr\Variable){
//                    echo "meizj";
                    $var = new Variable();
                    $var->BasicBlockId = $i;
                    $var->from = array($this->quads[$j]->arg2);
                    $var->linenum = array($this->quads[$j]->result->getStartLine(),$this->quads[$j]->result->getEndLine());
                    $var->Varname = $this->quads[$j]->result->name;
                    $this->addVar($var);
                }
                if($this->quads[$j]->op == "Expr_ArrayDimFetch" && $this->quads[$j]->arg1 instanceof PhpParser\Node\Expr\Variable){
                    // $_GET、$_POST、$_REQUEST
                    $type = $this->quads[$j]->arg1->name;
                    $sources = new Sources();
                    $input = $sources->getUserInput();
                    if(in_array($type,$input)){
                        $var = new Variable();
                        $var->BasicBlockId = $i;
                        $var->from = $type;
                        $var->linenum = array($this->quads[$j]->arg2->getStartLine(),$this->quads[$j]->arg2->getEndLine());
                        if($this->quads[$j]->arg2 instanceof PhpParser\Node\Scalar\String_){
                            $var->Varname = $this->quads[$j]->arg2->value;
                        }
                        $var->tainted = 1;
                        $this->addVar($var);
                    }
                }

            }
        }
    }
    public function varTaintCheck(){

        foreach ($this->var as &$item){
            $result = $this->varAnalyze($item);
            if($result !== null){
                $item->tainted = $result;
            }
        }
    }
    public function varAnalyze($item){
        $sources = new Sources();
        $input = $sources->getUserInput();

        if($item->tainted != null){
            return $item->tainted;
        }
        if($item->tainted != 1){
            if(is_array($item->from)){
                if(count($item->from) == 2){
                    if(in_array(null,$item->from) && ($item->from[0] instanceof PhpParser\Node\Scalar\String_ || $item->from[1] instanceof PhpParser\Node\Scalar\String_)){
                        // 来源中，两个操作数，一个为空，一个为字符串，则排除污点可能
//                        $item->tainted = 0;
//                        var_dump($item);
//                        echo "11";
                        return 0;
                    }
                    if($item->from[0] == null && $item->from[1] == null ){
                        return 0;
                    }
                    if($item->from[0] instanceof PhpParser\Node\Scalar\String_ && $item->from[1] instanceof PhpParser\Node\Scalar\String_){
                        return 0;
                    }
                    if(is_numeric($item->from[0]) || is_numeric($item->from[1])){
                        $result1 = $this->checktaint($item->from[0]);
                        $result2 = $this->checktaint($item->from[1]);
                        if($result1 || $result2){
                            return 1;
                        }else{
                            return 0;
                        }
                    }
                }elseif (count($item->from) == 1){
                    if($this->checktaint($item->from[0])){
                        return 1;
                    }else{
                        return 0;
                    }
                }
            }
        }



    }

    public function checktaint($id){
        /*
         * 循环遍历变量的from代表的id，以判断是否taint
         * 后面有时间会尝试用格来做依赖分析以及别名
         */
        if($id == null){
            return 0;
        }
        $quad = $this->quads[$id];



        if(stristr($quad->op,"PARAM_")){
            if($quad->result instanceof PhpParser\Node\Arg){
                if($quad->result->value instanceof PhpParser\Node\Expr\Variable){
                    foreach($this->var as $item){
                        if($item->Varname == $quad->result->value->name){
                            $result = $this->varAnalyze($item);
                            return $result;
                        }
                    }
                }
            }
        }
        if(in_array($quad->op,array("Expr_BinaryOp_Concat","Expr_Assign","Param"))){
            if($quad->arg1 instanceof PhpParser\Node\Expr\Variable || $quad->arg2 instanceof PhpParser\Node\Expr\Variable){
                if($quad->arg1 instanceof PhpParser\Node\Expr\Variable){
                    foreach($this->var as $item){
                        if($item->Varname == $quad->arg1->name){
                            $result = $this->varAnalyze($item);
                            return $result;
                        }
                    }
                }
                if($quad->arg2 instanceof PhpParser\Node\Expr\Variable){
                    foreach($this->var as $item){
                        if($item->Varname == $quad->arg2->name){
                            $result = $this->varAnalyze($item);
                            return $result;
                        }
                    }
                }
            }
            if(is_numeric($quad->arg1) || is_numeric($quad->arg2)){

                if(is_numeric($quad->arg1)){
                    $result = $this->checktaint($quad->arg1);
                }else{
                    $result = $this->checktaint($quad->arg2);
                }
                return $result;
            }
        }
        if($quad->op == "Expr_ArrayDimFetch" && $quad->arg1 instanceof PhpParser\Node\Expr\Variable){
            $sources = new Sources();
            $input = $sources->getUserInput();
            if(in_array($quad->arg1->name,$input)){
                return 1;
            }
        }
        if($quad->op == "Expr_FuncCall_End"){
            for($i=$quad->arg1;$i<=$quad->arg2;$i++){
                if($this->quads[$i]->op == "Stmt_Return"){
                    $result = $this->checktaint($this->quads[$i]->arg1);
                    return $result;
                }
            }
        }
        if($quad->op == "Expr_FuncCall_Internal_End"){
            for($i=$quad->arg1;$i<=$quad->arg2;$i++){
                if(stristr($this->quads[$i]->op,"PARAM_")){
                    $result = $this->checktaint($i);
                    return $result;
                }
            }
        }
        return 0;
    }

    public function addVar($var){
        if(!in_array($var,$this->var)){
            array_push($this->var,$var);
        }
    }
    public function addEdge($edge){
        if(!in_array($edge,$this->edge_info)){
            array_push($this->edge_info,$edge);
        }
    }
    public function analyze(){

//        var_dump($this->quads);
        // 遍历所有的基本块，如果发生了敏感函数调用，则回溯变量
        global $F_SINK_ALL;
        $idRange = array();
//        var_dump($this->cfg);
        $related_edge = array();
        $this->sinks = array();
        for($i=0;$i<=count($this->cfg);$i++){
            $currentCFG = $this->cfg[$i];
            $currentHead = $currentCFG->head;
            $currentTail = $currentCFG->tail;
            foreach ($currentHead->inedge as $inedge){
                if(!in_array($inedge,$related_edge)){
                    array_push($related_edge,$inedge);
                }
                for($j=0;$j<count($this->edge_info);$j++){
                    if($inedge->dest->outedge->id == $this->edge_info[$j]->from->inedge->id){
                        array_push($related_edge,$this->edge_info[$j]);
                        $inedge = $this->edge_info[$j];
                    }
                }
            }
            foreach ($related_edge as $edge){
                $start_id = $edge->from->inedge->id;
                if($edge->dest == null){
                    $end_id = count($this->quads) -1;
                }else{
                    $end_id = $edge->dest->outedge->id;
                }
                for($i=$start_id;$i<=$end_id;$i++){
                    // 敏感调用还未做完，仅考虑了少量情况
                    if($this->quads[$i]->op == "Expr_FuncCall_Internal" || $this->quads[$i]->op == "Stmt_Echo"){
                        if($this->quads[$i]->op == "Expr_FuncCall_Internal"){
                            $func_name = $this->quads[$i]->arg1->parts[0];
                        }elseif ($this->quads[$i]->op == "Stmt_Echo"){
                            $func_name = "echo";
                        }
                        if(array_key_exists($func_name,$F_SINK_ALL)){
                            if($this->quads[$i]->op == "Expr_FuncCall_Internal"){
                                $argName = $this->quads[$i-$this->quads[$i]->arg2]->result->value->name;
                                $startline = $this->quads[$i-$this->quads[$i]->arg2]->result->getStartLine();
                                $args = $this->quads[$i-$this->quads[$i]->arg2]->result->value;
                            }elseif ($this->quads[$i]->op == "Stmt_Echo"){
                                $argName = $this->quads[$i]->arg2[0]->name;
                                if(is_array($this->quads[$i]->arg2)){
                                    $startline = $this->quads[$i]->arg2[0]->getStartLine();
                                    $args = $this->quads[$i]->arg2[0];
                                }
                            }
                            foreach ($this->var as $item){
                                if($item->Varname == $argName){
                                    if($item->tainted == 1){
                                        $sink = new Sink();
                                        $sink->linenum = $startline;
                                        $sink->args = $args;
                                        $sink->type = $F_SINK_ALL[$func_name][1]["__NAME__"];
                                        $sink->name = $func_name;
                                        if(!in_array($sink,$this->sinks)){
                                            array_push($this->sinks,$sink);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($this->quads[$i]->op == "Expr_FuncCall"){
                        if(is_numeric($this->quads[$i]->arg1)){
                            if($this->quads[$this->quads[$i]->arg1]->result instanceof PhpParser\Node\Expr\Variable){
                                $argName = $this->quads[$this->quads[$i]->arg1]->result->name;
                                $startline = $this->quads[$i-$this->quads[$i]->arg2]->result->getStartLine();
                                foreach ($this->var as $item){
                                    if($item->Varname == $argName){
                                        if($item->tainted == 1){
                                            $sink = new Sink();
                                            $sink->linenum = $startline;
                                            $sink->args = $args;
                                            $sink->type = "EXEC";
                                            $sink->name = "\$".$argName;
                                            if(!in_array($sink,$this->sinks)){
                                                array_push($this->sinks,$sink);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }

                }
            }
        }
    }




}