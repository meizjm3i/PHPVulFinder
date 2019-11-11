<?php

define("SITE_ROOT",str_replace("\\","/",__DIR__));
require 'vendor/autoload.php';
require 'visitor.php';
require 'AST2IR.php';

require 'block/BasicBlock.php';
require 'block/BlockDivide.php';