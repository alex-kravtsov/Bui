<?php

class buiReplacer {

  private $patterns = null;

  public function load(){

    $this->patterns = array();

    $handle = fopen(BUI_BASE_PATH . '/config/replace.json', "r");

    if(!$handle) throw new Exception("Cannot open replacement configuration.");

    $records = array();

    while($buffer = fgets($handle) ){
      $records[] = $buffer;
    }

    fclose($handle);

    $pattern = null;

    foreach($records as $record){
      $pattern = json_decode($record);
      if(!empty($pattern) ) $this->patterns[] = $pattern;
    }

    foreach($this->patterns as &$item){
      $item->pattern = addcslashes($item->pattern, '.[]{}()^$?*');
      $item->columns = explode(',', $item->columns);
    }

  }

  public function replace($str, $index){

    foreach($this->patterns as $pattern){
      foreach($pattern->columns as $column){
        if($column == $index) $str = mb_ereg_replace($pattern->pattern, $pattern->replacement, $str);
      }
    }

    return $str;

  }

}
