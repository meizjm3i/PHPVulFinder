<?php

/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/10/29
 * Time: 11:28 AM
 */
$RETURN_STATEMENT = array('Stmt_Return') ;
$STOP_STATEMENT = array('Stmt_Throw','Stmt_Break','Stmt_Continue') ;
$LOOP_STATEMENT = array('Stmt_For','Stmt_While','Stmt_Foreach','Stmt_Do') ;
$JUMP_STATEMENT = array('Stmt_If','Stmt_Switch','Stmt_TryCatch','Expr_Ternary','Expr_BinaryOp_LogicalOr') ;
class AST2IR{

    /*
     * AST2IR : parse AST to futher IR , then IR to CFG
     */


    /*
     * parse AST to IR
     *
     * 1. Parse AST To Quad
     * 2. Generate Basic Block
     * 3. Generate Contorl Flow Graph
     *
     */

    public $quadId ;
    public $quads;
    public $funcs;
    public function __construct()
    {
        $this->quadId = -1;
    }

    public function parse($nodes){
        if(!is_array($nodes)){
            $nodes = array($nodes);
        }
        //$this->generateQuad($nodes);
        $this->quadId += 1;
        $this->quads[$this->quadId] = new Quad(0,$this->quadId);
        $this->funcs = array();
        $this->build_classTable($nodes);
        $this->build_funcTable($nodes);
        $this->test($nodes);
        $this->simulate();
        $flow_graph = new FlowGraphs();
        $flow_graph->BlockDivide($this->quads,$this->funcs);
        $flow_graph->optimize($this->quads);
    }

    public function simulate(){
        /*
         * 将四元组里的变量等成员换为等价的quad id
         */

    }

    public function SimpleParse($nodes){
        if(!is_array($nodes)){
            $nodes = array($nodes);
        }
        $this->quadId += 1;
        $this->quads[$this->quadId] = new Quad(0,$this->quadId);
        $this->funcs = array();
        $this->build_classTable($nodes);
        $this->build_funcTable($nodes);
        $this->test($nodes);
        $this->parseFunction();
        return $this->quads;
    }


    /*
     * 建立函数表
     * 在进行四元组生成时，暂时不生成用户自定义函数的四元组，等进行基本块划分再对自定义函数直接生成基本块
     */
    public function build_funcTable($nodes){
        if(!is_array($nodes)){
            $nodes = array($nodes);
        }

        foreach ($nodes as $node){
            if($node instanceof PhpParser\Node\Stmt\Function_){
                $func_name   = $node->name;
                $func_params_count = count($node->params);
                $func_params = $node->params;
                $func_stmt   = $node->stmts;
                $hash = md5($func_name->name);
                $this->funcs[$hash] = new func_table($func_name,$func_stmt,$func_params,$func_params_count,false,null,'\\');
            }

        }
    }



    public function build_classTable($nodes){

    }



    public  function test($nodes){
        global $JUMP_STATEMENT,$RETURN_STATEMENT,$LOOP_STATEMENT,$STOP_STATEMENT;

        if(!is_array($nodes)){
            $nodes = array($nodes);
        }

        foreach ($nodes as $node){

            if(in_array($node->getType(),$JUMP_STATEMENT)){

                $this->getBranches($node);

            }elseif (in_array($node->getType(),$RETURN_STATEMENT)){


            }elseif(in_array($node->getType(),$LOOP_STATEMENT)){
                $this->addLoop($node);

            }elseif (in_array($node->getType(),$STOP_STATEMENT)){


            }else{
                //$this->var_debug($node);

                if($node instanceof PhpParser\Node\Stmt){
                    $this->StmtParse($node);
                }
            }
        }
    }
    public function addLoop($node){
        switch ($node->getType()){
            case 'Stmt_For':  //for(i=0;i<3;i++) ===> extract var i
                $this->StmtParse($node);
                break ;
            case 'Stmt_While':  //while(cond) ====> extract cond

                break ;
            case 'Stmt_Foreach':  //foreach($nodes as $node) ======> extract $nodes

                break ;
            case 'Stmt_Do':   //do{}while(cond); =====> extract cond

                break ;
        }
    }

    public function getBranches($node){

        $type = $node->getType();

        switch ($type){
            case 'Stmt_If':
                $this->conditonParse($node);
                break;
            case 'Stmt_Switch':
                $this->parseSwitch($node);
                break;
            case 'Stmt_TryCatch':
                break;

            case 'Expr_Ternary':
                break;

            case 'Expr_BinaryOp_LogicalOr':
                break;
        }


    }
    /*
     * 将if语句中条件部分的AST转换为四元组
     * 最后一组四元组的值为1，goto stmts
     * 最后一组四元组的值为0，goto elseifs
     * 需要通过回填来完成goto的标号
     *
     * 将else-if语句中条件部分的AST转换为四元组
     * 最后一组四元组的值为1，goto stmts
     * 最后一组四元组的值为0，如果有其余的else-if，则跳转到下一个else-if
     * 最后一组四元组的值为0，如果无其余的else-if，则跳转到else
     *
     * else语句无条件判断，直接进入stmts
     *
     *
     * Notice: 语法规则只符合if，不能用于switch等语句的condition的解析
     */
    public function conditonParse($node){
        $cond = $node->cond;
        $elseifs = $node->elseifs;
        $elseifs_count = count($elseifs);
        $else = $node->else;
        $this->ExprParse($cond,0);
        $if_pos = $this->quadId;
        // 新增一个判断节点
        // 跳转地址暂空
        $this->quadId += 1;


        $this->quads[$this->quadId] = new Quad(0,$this->quadId,"JUMP",$this->quads[$if_pos]->result,0);
        if($node->stmts != null){
            $this->test($node->stmts,null);
        }

        $elseif_pos_arr = array();
        for($i = 0;$i < $elseifs_count;$i++){
            $this->ExprParse($elseifs[$i]->cond,0);
            if($i == 0){
                $this->quads[$if_pos+1]->set_result($elseif_pos_arr[0]);
            }
            $this->quadId += 1;
            $id = $this->quadId ;
            $this->quads[$this->quadId] = new Quad(1,$this->quadId,"JUMP",$this->quads[$if_pos]->result,0);
            array_push($elseif_pos_arr,$this->quadId);
            if($elseifs[$i]->stmts != null){
                $this->test($elseifs[$i]->stmts,null);
            }
        }
        $id = $this->quadId;
        if($else->stmts != null){
            $this->test($else->stmts,0);
        }

        // 开始回填

        for($i=0;$i<$elseifs_count;$i++){
            $this->quads[$elseif_pos_arr[$i]]->set_result($id);
        }


    }
    /*
     *
     *
     * Notice：为了兼容后面的逻辑，条件判断时尽量使用elseif，避免else的使用
     */
    public function ExprParse($expr,$debug=0){
        if($debug == 1){
            var_dump($expr);
        }

        if($expr instanceof  PhpParser\Node\Expr\BinaryOp\BooleanAnd ||
            $expr instanceof PhpParser\Node\Expr\BinaryOp\BooleanOr ||
            $expr instanceof PhpParser\Node\Expr\BinaryOp\Equal ||
            $expr instanceof PhpParser\Node\Expr\BinaryOp\Concat ||
            $expr instanceof PhpParser\Node\Expr\BinaryOp\Smaller
        ){
            /*
             * 布尔运算符 && 、 || 、 == 、Concat 的处理，目前仅考虑常见的两种情况:
             * 1. 某一边存在多重运算
             * 2. 两边都为常数
             */


            if($expr->left instanceof PhpParser\Node\Expr\BinaryOp || $expr->right instanceof PhpParser\Node\Expr\BinaryOp ){
                /*
                 * 布尔运算符的两边至少一边有二进制操作，比如 2 && (3==1)
                 */
                if($expr->left instanceof PhpParser\Node\Expr\BinaryOp && $expr->right instanceof PhpParser\Node\Expr\BinaryOp){
                    $this->ExprParse($expr->left);
                    $left_id = $this->quadId;
                    $this->ExprParse($expr->right);
                    $right_id = $this->quadId;
                    $this->quadId += 1;
                    $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$left_id,$right_id,"temp_$this->quadId");
                }elseif ($expr->left instanceof PhpParser\Node\Expr\BinaryOp){
                    $this->ExprParse($expr->left);
                    $left_id = $this->quadId;
                    if($expr->right instanceof PhpParser\Node\Scalar){
                        $this->quadId += 1;
                        $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$left_id,$expr->right,"temp_$this->quadId");
                    }
                }elseif ($expr->right instanceof PhpParser\Node\Expr\BinaryOp){

                    $this->ExprParse($expr->right);
                    $right_id = $this->quadId;
                    if($expr->left instanceof PhpParser\Node\Scalar){
                        $this->quadId += 1;
                        $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$right_id,$expr->left,"temp_$this->quadId");
                    }
                }
            }elseif ($expr->left instanceof PhpParser\Node\Scalar || $expr->right instanceof PhpParser\Node\Scalar){

                if(($expr->left instanceof PhpParser\Node\Scalar || $expr->left instanceof PhpParser\Node\Expr\Variable) &&
                    $expr->right instanceof PhpParser\Node\Scalar
                ){
                    $this->quadId += 1;
                    $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$expr->left,$expr->right,"temp_$this->quadId");
                }

            }elseif ($expr->left instanceof PhpParser\Node\Expr\Variable &&  $expr->left instanceof PhpParser\Node\Expr\Variable){
                $this->quadId += 1;
                $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$expr->left,$expr->right,"temp_$this->quadId");
            }
        }


        if($expr instanceof PhpParser\Node\Expr\Assign){

            /*
             * 对于赋值语句只做了左值是单变量的处理，对于复杂表达式的处理以后加
             *
             * todoList + 1
             *
             */
            if($expr->expr instanceof PhpParser\Node\Scalar){
                /*
                 * 生成一个四元组，对于expr是常量的情况，直接进行ASSIGN四元组的生成
                 *
                 */

//                $this->var_debug($expr);
                $this->quadId += 1;
                $id = $this->quadId ;
                $this->quads[$id] = new Quad(0,$this->quadId,$expr->getType(),null,$expr->expr,$expr->var);

            }elseif($expr->expr instanceof PhpParser\Node\Expr){
                $this->ExprParse($expr->expr,0);
                $now_id = $this->quadId;

                if($expr->var instanceof PhpParser\Node\Expr\Variable){
                    $this->quadId += 1;
                    $id = $this->quadId;
                    $this->quads[$id] = new Quad(0,$this->quadId,$expr->getType(),null,$now_id,$expr->var);
                }
            }
        }


        if($expr instanceof PhpParser\Node\Expr\PostInc ||
            $expr instanceof PhpParser\Node\Expr\PostDec
        ){
            $this->quadId += 1;
            $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$expr->var,null,null);
        }

        if($expr instanceof PhpParser\Node\Expr\FuncCall){
            $param_count = count($expr->args);
            for($i=0;$i<$param_count;$i++){
                $this->quadId += 1;
                $this->quads[$this->quadId] = new Quad(0,$this->quadId,"PARAM_$i",null,null,$expr->args[$i]);
            }
            $this->quadId += 1;
            $id = $this->quadId;
            $this->quads[$this->quadId] = new Quad(1,$this->quadId,$expr->getType(),$expr->name,$param_count,null);

            if(!function_exists($expr->name)){
                if(array_key_exists(md5($expr->name),$this->funcs)){
                    $this->StmtParse($this->funcs[md5($expr->name)]->func_stmt);
//                    echo 123;
                }
            }

            $this->quads[$id]->result = $this->quadId+1;
        }

        if($expr instanceof PhpParser\Node\Expr\ArrayDimFetch){
            $this->quadId += 1;
            $this->quads[$this->quadId] = new Quad(0,$this->quadId,$expr->getType(),$expr->var,$expr->dim,"temp_$this->quadId");
        }


    }


    /*
     * 将 Switch-Case-Default 语句的AST转换为四元组
     *
     * 分为三个部分:
     *  1. Switch解析
     *  2. Case解析
     *  3. Default解析
     *
     * Switch解析本质上是个值判断，无论中间表达式的复杂性，最终都可以将值统一在一个临时变量上
     *
     * 首先将Switch语句转换为四元组，随后根据case条件的个数生成连续的JUMP四元组，其中跳转条件需要等待回填
     *
     * case处理时注意提取case的条件以及每个case对应stmt的开始标号
     *
     * default语句其实就是值恒为真的case
     *
     * Notice: 注意break、Continue、Continue 2导致的跳转情况
     */

    public function parseSwitch($node){
        $cases_count = count($node->cases);
        $JUMP_ID = array();

        if($node->cond instanceof PhpParser\Node\Expr){

            if($node->cond instanceof PhpParser\Node\Expr\Variable){
                for($i = 0;$i<$cases_count;$i++){
                    $this->quadId += 1;
                    $JUMP_ID[$i] = $this->quadId;
                    $this->quads[$this->quadId] = new Quad(1,$this->quadId,"JUMP",$node->cond);
                    /*
                     * arg2 与 result 需要在解析完case后再进行回填
                     */
                }
            }else{
                $this->ExprParse($node->cond,0);
                $now_id = $this->quadId;
                for($i = 0;$i<$cases_count;$i++){
                    $this->quadId += 1;
                    $JUMP_ID[$i] = $this->quadId;
                    $this->quads[$this->quadId] = new Quad(1,$this->quadId,"JUMP",$this->quads[$now_id]->result);
                    /*
                     * arg2 与 result 需要在解析完case后再进行回填
                     */
                }
            }
        }


        /*
         * 开始处理 cases
         * cases是一个数组，里面存放是各个case，使用foreach处理
         *
         * 每个case有 cond 和 stmts
         *
         * stmts里面的跳转节点可能有 break, continue, continue 2
         *
         * break与continue的跳转流程类似，continue 2与这两者不同
         */
        $case_cond_array = array();
        $case_stmt_array = array();
        $jump_array = array();


        foreach ($node->cases as $case) {
            if($case->cond instanceof PhpParser\Node\Scalar){
                $this->ScalarParse($case->cond);
                array_push($case_cond_array,$this->quadId);
            }elseif($case->cond instanceof PhpParser\Node\Expr){
                $this->ExprParse($case->cond);
                array_push($case_cond_array,$this->quadId);
            }elseif($case->cond == null){
                array_push($case_cond_array,$node->cond);
            }
            array_push($case_stmt_array,$this->quadId+1);
            $this->StmtParse($case->stmts);
            array_push($jump_array,$this->quadId);
        }

        foreach ($jump_array as $jump_id){
            switch ($this->quads[$jump_id]->op){
                case 'Stmt_Break':
                    $this->quads[$jump_id]->result = $this->quadId + 1;
                    break;
                case 'Stmt_Continue':
                    $this->quads[$jump_id]->result = $this->quadId + 1;
                    break;
                case 'Stmt_Continue_2':
                    // 跳到外层循环中
                    // 先随便给个值，在for解析中回填
                    $this->quads[$jump_id]->result = $this->quadId + 1;
                    break;
            }
        }

        for($i = 0;$i<$cases_count;$i++) {
            $id = $JUMP_ID[$i];
            $case_cond = $case_cond_array[$i];
            $case_stmt = $case_stmt_array[$i];
            $this->quads[$id]->set_arg2($case_cond);
            $this->quads[$id]->set_result($case_stmt);
        }
        /*
         * 回填break、continue、continue 2的跳转地址
         */
    }

    public function StmtParse($stmt){
        if(is_array($stmt)){
            foreach ($stmt as $stmt_single){
                $this->StmtParse($stmt_single);
            }
        }
        if($stmt instanceof PhpParser\Node\Stmt\Expression){

            $this->ExprParse($stmt->expr);
        }
        if($stmt instanceof PhpParser\Node\Stmt\Break_ ||
            $stmt instanceof PhpParser\Node\Stmt\Continue_
        ){

            if($stmt->num instanceof PhpParser\Node\Scalar\LNumber &&
                $stmt->num->value == 2
            ){
                $this->quadId += 1;
                $this->quads[$this->quadId] = new Quad(1,$this->quadId,$stmt->getType()."_2",null,null,null);
            }elseif ($stmt->num == null){
                $this->quadId += 1;
                $this->quads[$this->quadId] = new Quad(1,$this->quadId,$stmt->getType(),null,null,null);
            }
        }
        if($stmt instanceof PhpParser\Node\Stmt\Echo_){
            $this->quadId += 1;
            $this->quads[$this->quadId] = new Quad(0,$this->quadId,$stmt->getType(),null,$stmt->exprs,null);
        }
        if($stmt instanceof PhpParser\Node\Stmt\For_){

            if(is_array($stmt->init)){
                foreach ($stmt->init as $init){
                    $this->ExprParse($init);
                }
            }else{
                $this->ExprParse($stmt->init,1);
            }

            if(is_array($stmt->cond)){
                foreach ($stmt->cond as $cond){
                    $this->ExprParse($cond);
                }
            }else{
                $this->ExprParse($stmt->cond,1);
            }
            $cond_id = $this->quadId;
            $this->quadId +=1 ;
            $this->quads[$this->quadId] = new Quad(0,$this->quadId,"JUMP",$cond_id,0,null);
            if(is_array($stmt->loop)){
                foreach ($stmt->loop as $loop){
                    $this->ExprParse($loop,0);
                }
            }else{
                $this->ExprParse($stmt->loop,1);
            }

            $loop_id = $this->quadId;
            if(is_array($stmt->stmts)){
                foreach ($stmt->stmts as $stmts){
                    $this->test($stmts);
                }
            }else{
                $this->test($stmt->stmts);
            }

            $this->quads[$this->quadId]->set_result($cond_id);
            $this->quads[$cond_id + 1]->set_result($this->quadId+1);

            for($i=$loop_id;$i<=$this->quadId;$i++){
                if($this->quads[$i]->op == "Stmt_Continue_2"){
                    $this->quads[$i]->set_result($cond_id);
                }
            }

        }
    }

    public function ScalarParse($Scalar){
        echo "****";
        if($Scalar instanceof PhpParser\Node\Scalar\String_){
            $this->quadId += 1;
            $this->quads[$this->quadId] = new Quad(0,$this->quadId,"Expr_Assign",null,$Scalar->value,"temp_$this->quadId");
        }
    }

    public function parseTryCatch(){

    }

    public function parseTernary(){

    }

    public function var_debug($debug){
        if(is_array($debug)){
            foreach ($debug as $d){
                var_dump($d);
            }
        }else{
            var_dump($debug);
        }
    }


}



class Quad{
    /*
     * Quad : 四元组
     *
     * ("JUMP",arg1,arg2,result)   如果arg1和arg2相等，则跳转到result标号对应的quad
     * ("Expr_FuncCall",arg1,arg2,result)   arg1为函数名，arg2为字段数量，在FuncCall四元组上方是连续的字段四元组，arg2限定读取的字段的数量
     * ("ASSIGN",null,arg2,result)  将arg2的值赋值给result
     *
     */
    public $label; // 是否跳转，0：不跳转  ， 1：跳转到别处 ， 2：跳转到此处
    public $id;
    public $op;  // 操作
    public $arg1; // 左操作数
    public $arg2; // 左操作数
    public $result; // 结果

    public function __construct( $label = 0 ,$id,$op = null , $arg1 = null , $arg2 = null , $result = null){

        $this->label  = $label;
        $this->id     = $id;
        $this->op     = $op;
        $this->arg1   = $arg1;
        $this->arg2   = $arg2;
        $this->result = $result;
    }

    /*
     * set_op : set operation
     * get_op : get operation
     */
    public function set_op($op){
        $this->op = $op;
    }
    public function get_op(){
        return $this->op;
    }

    /*
     * set_arg1 : set arg1
     * get_arg1 : get arg1
     */
    public function set_arg1($arg1){
        $this->arg1 = $arg1;
    }
    public function get_arg1(){
        return $this->arg1;
    }
    /*
     * set_arg2 : set arg2
     * get_arg2 : get arg2
     */
    public function set_arg2($arg2){
        $this->arg2 = $arg2;
    }
    public function get_arg2(){
        return $this->arg2;
    }
    /*
     * set_result : set result
     * get_result : get result
     */

    public function set_result($result){
        $this->result = $result;
    }
    public function get_result(){
        return $this->result;
    }


    public function set_label($label){
        $this->label = $label;
    }

    public function get_label(){
        return $this->label;
    }


}





