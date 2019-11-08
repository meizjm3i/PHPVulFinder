<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/10/23
 * Time: 11:22 AM
 */
require  'global.php';



function load_file($name,$ver){
    if($ver = '5'){
        $parser = new PhpParser\Parser\Php5(new PhpParser\Lexer\Emulative()) ;
    }elseif ($ver = '7'){
        $parser = new PhpParser\Parser\Php7(new PhpParser\Lexer\Emulative()) ;
    }

    $code = file_get_contents($name);
    $parseResult = $parser->parse($code);
    $NodeInitVisitor = new NodeInitVisitor;
    $traverser = new PhpParser\NodeTraverser();
    $traverser->addVisitor($NodeInitVisitor);
    $traverser->traverse($parseResult);
    $nodes  = $NodeInitVisitor->getNodes();
    $ast2ir = new AST2IR();
    return $ast2ir->parse($nodes);

}

$Name = $_POST['path'];

if(is_dir($Name)){
    exit("Not yet.");
}elseif (is_file($Name)){
    var_dump(load_file($Name,5));
}