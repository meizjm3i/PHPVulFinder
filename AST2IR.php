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
        $quad = new Quad($this->quadId);

        $this->quads[$this->quadId] = $quad;
        $this->test($nodes,null);

        $blockDivide = new BlockDivide();

        $ir = $blockDivide->SimulateIR($this->quads);

        $ir = $blockDivide->IROptimize($ir);

        $blocks = $blockDivide->ParseIR($ir);
    }

    public  function test($nodes,$condition){
        global $JUMP_STATEMENT,$RETURN_STATEMENT,$LOOP_STATEMENT,$STOP_STATEMENT;

        if(!is_array($nodes)){
            $nodes = array($nodes);
        }

        foreach ($nodes as $node){
            //JUMP
            if(in_array($node->getType(),$JUMP_STATEMENT)){
                $this->getBranches($node);
                // Return
            }elseif (in_array($node->getType(),$RETURN_STATEMENT)){


                // Loop
            }elseif(in_array($node->getType(),$LOOP_STATEMENT)){


                // Stop
            }elseif (in_array($node->getType(),$STOP_STATEMENT)){


            }else{
                $this->ExprParse($node,0);
            }
        }
    }

    public function getBranches($node){

        $type = $node->getType();

        switch ($type){
            case 'Stmt_If':
                // 处理 if-elseif-else语句中的条件，跳转关系使用四元组存储
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
//        var_dump($this->quads);

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
        $id = $this->quadId;

        $this->quads[$id] = new Quad($id);
        $this->quads[$id]->set_op("JUMP");
        $this->quads[$id]->set_arg1($this->quads[$if_pos]->result);
        $this->quads[$id]->set_arg2(0);
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
            $this->quads[$id] = new Quad($id);
            $this->quads[$id]->set_op("JUMP");
            $this->quads[$id]->set_arg1($this->quads[$if_pos]->result);
            $this->quads[$id]->set_arg2(0);
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

    public function ExprParse($expr,$debug){
        if($debug == 1){
            var_dump($expr);
        }
        if($expr instanceof PhpParser\Node\Expr\BinaryOp\BooleanAnd || $expr instanceof PhpParser\Node\Expr\BinaryOp\BooleanOr){
            $this->ExprParse($expr->left,0);
            $this->ExprParse($expr->right,0);
        }
        if($expr instanceof PhpParser\Node\Expr\BinaryOp){
            $this->quadId += 1;
            $id = $this->quadId ;
            $this->quads[$id] = new Quad($id);
            $this->quads[$id]->set_label(0);
            $expr->setAttribute("quad_id",$id);

            if($expr->left instanceof PhpParser\Node\Expr\BinaryOp ){
                $attr = $expr->left->getAttributes();
                $this->quads[$id]->set_arg1($attr['quad_id']);
            }else{
                $this->quads[$id]->set_arg1($expr->left);
            }
            if($expr->right instanceof PhpParser\Node\Expr\BinaryOp ){
                $attr = $expr->right->getAttributes();
                $this->quads[$id]->set_arg2($attr['quad_id']);
            }else{
                $this->quads[$id]->set_arg2($expr->right);
            }
            $this->quads[$id]->set_op($expr->getOperatorSigil());
            $this->quads[$id]->set_result("temp_$id" );
        }
        if($expr instanceof PhpParser\Node\Stmt\Expression){
            $expression = $expr->expr;
            if($expression instanceof PhpParser\Node\Expr\Assign){
                if($expression->var instanceof PhpParser\Node\Stmt\Expression){
                    $this->ExprParse($expression->var,0);
                }
                if($expression->expr instanceof PhpParser\Node\Stmt\Expression){
                    $this->ExprParse($expression->expr,0);
                }
                $this->quadId += 1;
                $id = $this->quadId ;
                $this->quads[$id] = new Quad($id);
                $this->quads[$id]->set_label(0);
                $this->quads[$id]->set_op($expression->getType());
                $this->quads[$id]->set_arg1($expression->expr);
                $this->quads[$id]->set_arg2(null);
                $this->quads[$id]->set_result($expression->var);
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

                


            }elseif($expr->expr instanceof PhpParser\Node\Expr){
                $this->ExprParse($expr->expr,0);
                $now_id = $this->quadId;

                if($expr->var instanceof PhpParser\Node\Expr\Variable){
                    $this->quadId += 1;
                    $id = $this->quadId ;
                    $this->quads[$id] = new Quad($id);
                    $this->quads[$id]->set_label(0);
                    $this->quads[$id]->set_op($expr->getType());
                    $this->quads[$id]->set_arg1(null);
                    $this->quads[$id]->set_arg2($now_id);
                    $this->quads[$id]->set_result($expr->var);
                }
            }






        }

        if($expr instanceof PhpParser\Node\Expr\Variable){

        }

    }



    public function calcCondDepth($cond){
        if($cond == null){
            return 0;
        }
        $leftDepth = $this->calcCondDepth($cond->left);
        $rightDepth = $this->calcCondDepth($cond->right);
        return ((($leftDepth > $rightDepth) ? $leftDepth : $rightDepth) + 1);
    }

    public function parseSwitch($node){
//        var_dump($node);
        $cases_count = count($node->cases);
        var_dump($node->cond);
        if($node->cond instanceof PhpParser\Node\Expr){
            $this->ExprParse($node->cond,0);
        }

        $JUMP_ID = array();
        for($i = 0;$i<$cases_count;$i++){
            $this->quadId += 1;
            $this->quads[$this->quadId] = new Quad($this->quadId);
            $this->quads[$this->quadId]->set_op("JUMP");
            $JUMP_ID[$i] = $this->quadId;
        }
    }

    public function parseTryCatch(){

    }

    public function parseTernary(){

    }



}



class Quad{
    /*
     * Quad : 四元组
     *
     * ("JUMP",arg1,arg2,result)   如果arg1和arg2相等，则跳转到result标号对应的quad
     * ("ASSIGN",null,arg2,result)  将arg2的值赋值给result
     *
     */
    public $label; // 是否跳转，0：不跳转  ， 1：跳转到别处 ， 2：跳转到此处
    public $id;
    public $op;  // 操作
    public $arg1; // 左操作数
    public $arg2; // 左操作数
    public $result; // 结果

    public function __construct($id){
        $this->id = $id;
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
    public function set_id($id){
        $this->id = $id;
    }
    public function get_id(){
        return $this->id;
    }

}





