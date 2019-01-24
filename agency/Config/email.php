<?php
$file = ROOT.DS.'config'.DS.'email.php';
if(is_file($file)){
 require_once($file);
}