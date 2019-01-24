<?php
$file = ROOT.DS.'/config/const.php';
if(is_file($file)){
 require_once($file);
}
//include myTools
$file = CAKE_CORE_INCLUDE_PATH.'/Lib/myTools.php';
if(is_file($file)){ require_once($file); }
/*Autoload table class*/
spl_autoload_register(function($class){
  $classFile1 = CAKE_CORE_INCLUDE_PATH.'/Model/'.$class.'.php';
  $classFile2 = CAKE_CORE_INCLUDE_PATH.'/Lib/'.$class.'.php';
  $classFile3 = CAKE_CORE_INCLUDE_PATH.'/Model/Base/'.$class.'.php';
  if(is_file($classFile1)){ require_once($classFile1); }
  if(is_file($classFile2)){ require_once($classFile2); }
  if(is_file($classFile3)){ require_once($classFile3); }
});