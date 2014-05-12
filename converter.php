<?php

define("BUI_BASE_PATH", dirname(__FILE__) );

require_once BUI_BASE_PATH . '/config/config.php';
require_once BUI_BASE_PATH . '/models/products.php';

try {

  if(!date_default_timezone_set(buiConfig::TIMEZONE) ) throw new Exception("Invalid timezone identifier.");

  if(!mb_internal_encoding(buiConfig::INTERNAL_ENCODING) ) throw new Exception("Cannot set internal encoding.");

  if(!mb_regex_encoding(buiConfig::INTERNAL_ENCODING) ) throw new Exception("Cannot set regex encoding.");

  $model = new buiProducts();

  $model->load(buiConfig::INPUT_XML);
  $model->findCategories();
  $model->findProducts();
  $model->findOffers();
  $model->setProductCategories();
  $model->setProductPrices();

  $products = $model->getProducts();

  $output = "";
  
  $row_count = 0;
  
  $file_count = 1;

  foreach($products as $product){

    $output .= $product->id . ";";
    $output .= '"' . str_replace('"', '""', $product->title) . '";';
    $output .= $product->price . ";";
    $output .= '"' . str_replace('"', '""', $product->description) . '";';

    if(!empty($product->image) ){
      $output .= '"' . buiConfig::IMAGE_URL_BASE . $product->image . '";';
    }
    else $output .= ';';

    $output .= '"' . str_replace('"', '""', $product->category_title) . "\"\n";
    
    $row_count++;
    
    if($row_count >= buiConfig::ROW_LIMIT){
    
      file_put_contents(buiConfig::OUTPUT_CSV_PATTERN . "_$file_count.csv", $output);
      
      $output = "";
      
      $row_count = 0;
      
      $file_count++;
      
    }

  }

  file_put_contents(buiConfig::OUTPUT_CSV_PATTERN . "_$file_count.csv", $output);

}
catch(Exception $e){
  echo "Error:\n";
  echo "Message: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . "\n";
  echo "Line: " . $e->getLine() . "\n";
  echo "Trace:\n";
  echo $e->getTraceAsString() . "\n";
}
