<?php


require  'global.php';

$smarty = new \Smarty();
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";

$smarty->setTemplateDir(SITE_ROOT."/views/");
$smarty->setCompileDir(SITE_ROOT . '/cache/templates_c/');
$smarty->setCacheDir(SITE_ROOT."/cache/");

$smarty->assign('title','This is my first Composer Project!');
$smarty->display('index.html');


?>