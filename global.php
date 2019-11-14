<?php

define("SITE_ROOT",str_replace("\\","/",__DIR__));
require 'vendor/autoload.php';
require 'visitor.php';
require 'AST2IR.php';

require 'block/BasicBlock.php';
require 'block/Block.php';


require 'table/func_table.php';
require 'block/FlowGraphs.php';