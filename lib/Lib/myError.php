<?php
class myError{
  
  public static function validation($data){
    $errors = array();
    foreach($data as $vars){
      foreach($vars as $var){
        $errors[] = $var;
      }
    }
    return $errors;
  }
}