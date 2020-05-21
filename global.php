<?php

//ini_set("xdebug.var_display_max_children",128);
//ini_set("xdebug.var_display_max_data",512);
//ini_set("xdebug.var_display_max_depth",5);

define("SITE_ROOT",str_replace("\\","/",__DIR__));
require 'vendor/autoload.php';

require 'config/securing.php';
require 'config/sinks.php';
require 'config/sources.php';

require 'visitor.php';
require 'AST2IR.php';

require 'block/BasicBlock.php';
require 'block/FlowGraphs.php';
require 'block/Analysis.php';
require 'block/Graph.php';
require 'block/Sink.php';
require 'table/func_table.php';

