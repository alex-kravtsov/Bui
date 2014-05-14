<?php

define("BUI_BASE_PATH", dirname(__FILE__) );

require_once BUI_BASE_PATH . '/config/config.php';
require_once BUI_BASE_PATH . '/models/products.php';
require_once BUI_BASE_PATH . '/models/replacer.php';

try {

  if(!date_default_timezone_set(buiConfig::TIMEZONE) ) throw new Exception("Invalid timezone identifier.");

  if(!mb_internal_encoding(buiConfig::INTERNAL_ENCODING) ) throw new Exception("Cannot set internal encoding.");

  if(!mb_regex_encoding(buiConfig::INTERNAL_ENCODING) ) throw new Exception("Cannot set regex encoding.");

  $model_catalog = new buiProducts();

  $model_catalog->load(buiConfig::INPUT_XML);
  $model_catalog->findCategories();
  $model_catalog->findProducts();
  $model_catalog->setProductCategories();
  $model_catalog->findOffers();
  $model_catalog->setProductPrices();

  $products = $model_catalog->getProducts();

  unset($model_catalog);

  $model_replacer = new buiReplacer();
  $model_replacer->load();

  $output = "";

  $row_count = 0;

  $file_count = 1;

  foreach($products as $product){

    $output .= $product->id . ";";

    $product->title = $model_replacer->replace($product->title, buiConfig::PRODUCT_TITLE_COLUMN);
    $output .= '"' . str_replace('"', '""', $product->title) . '";';

    $output .= $product->price . ";";

    if(!empty($product->images) ){

      $images = array();
      foreach($product->images as $image){
        if($image == $product->image) continue;
        $images[] = buiConfig::IMAGE_URL_BASE . $image;
      }

      $template = file_get_contents(BUI_BASE_PATH . '/templates/images.html');

      $for_pattern = "\{%FOREACH_IMAGE%\}(.*)\{%ENDFOREACH%\}";
      $img_pattern = "\{%IMAGE_URL%\}";

      if(!mb_ereg($for_pattern, $template, $regs) ) throw new Exception("Cannot match template entities");

      $img_chunk = $regs[1];

      $for_chunk = "";

      foreach($images as $image){
        $for_chunk .= mb_ereg_replace($img_pattern, htmlspecialchars($image), $img_chunk);
      }

      $product->description .= mb_ereg_replace($for_pattern, $for_chunk, $template);

    }
    $product->description = $model_replacer->replace($product->description, buiConfig::PRODUCT_DESCRIPTION_COLUMN);
    $output .= '"' . str_replace('"', '""', $product->description) . '";';

    if(!empty($product->image) ){
      $output .= '"' . buiConfig::IMAGE_URL_BASE . $product->image . '";';
    }
    else $output .= ';';

    $category1 = $product->category;
    if(!empty($category1->parent) ) $category2 = $category1->parent;
    if(!empty($category2->parent) ) $category3 = $category2->parent;

    $category1->title = $model_replacer->replace($category1->title, buiConfig::PRODUCT_CATEGORY1_COLUMN);
    $output .= '"' . str_replace('"', '""', $category1->title) . '";';

    if(!empty($category2) ){

      $category2->title = $model_replacer->replace($category2->title, buiConfig::PRODUCT_CATEGORY2_COLUMN);
      $output .= '"' . str_replace('"', '""', $category2->title) . '";';

    }
    else {
      $output .= ';';
    }

    if(!empty($category3) ){

      $category3->title = $model_replacer->replace($category3->title, buiConfig::PRODUCT_CATEGORY3_COLUMN);
      $output .= '"' . str_replace('"', '""', $category3->title) . "\";\n";

    }
    else {
      $output .= ";\n";
    }

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
