<?php

class buiProducts {

  private $categories = null;
  private $products = null;
  private $offers = null;
  private $document = null;

  public function load($filename){

    $this->document = new DOMDocument();
    $this->document->load($filename);

  }

  public function findCategories(){

    $catalogs = $this->document->getElementsByTagName('Каталог');
    $catalog = $catalogs->item(0);

    $groups = $catalog->getElementsByTagName('Группа');

    $this->categories = array();

    foreach($groups as $group){

      $category = new stdClass();
      $category->id = $group->getAttribute('Идентификатор');
      $category->title = $group->getAttribute('Наименование');

      $this->categories[] = $category;

    }

  }

  public function findProducts(){

    $catalogs = $this->document->getElementsByTagName('Каталог');
    $catalog = $catalogs->item(0);

    $items = $catalog->getElementsByTagName('Товар');

    $this->products = array();

    foreach($items as $item){
      
      $product = new stdClass();

      $product->id = $item->getAttribute('Идентификатор');
      $product->title = $item->getAttribute('Наименование');
      $product->category_id = $item->getAttribute('Родитель');
      $product->description = $this->_getProductOption($item, 'Описание');

      $product->image = $this->_getProductOption($item, 'КартинкаБольшая');

      if(empty($product->image) ){
        $product->image = $this->_getProductOption($item, 'Картинка1');
      }
      
      $this->products[] = $product;

    }

  }

  private function _getProductOption($item, $option_id){

    $option_value = null;

    $options = $item->getElementsByTagName('ЗначениеСвойства');

    foreach($options as $option){

      $oid = $option->getAttribute('ИдентификаторСвойства');

      if($oid == $option_id){
        $option_value = $option->getAttribute('Значение');
        break;
      }

    }

    return $option_value;

  }

  public function findOffers(){

    $offer_packs = $this->document->getElementsByTagName('ПакетПредложений');
    $offer_pack = $offer_packs->item(0);

    $items = $offer_pack->getElementsByTagName('Предложение');

    $this->offers = array();

    foreach($items as $item){
      
      $offer = new stdClass();

      $offer->product_id = $item->getAttribute('ИдентификаторТовара');
      $offer->price = $item->getAttribute('Цена');

      $this->offers[] = $offer;

    }

  }

  public function setProductCategories(){

    foreach($this->products as &$product){

      $product->category_title = null;

      foreach($this->categories as $category){

        if($product->category_id == $category->id){
          $product->category_title = $category->title;
          break;
        }

      }

    }

  }

  public function setProductPrices(){
    
    foreach($this->products as &$product){

      $product->price = 0;

      foreach($this->offers as $offer){

        if($product->id == $offer->product_id){
          $product->price = $offer->price;
          break;
        }

      }

    }

  }

  public function getProducts(){
    return $this->products;
  }

}
