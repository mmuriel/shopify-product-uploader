<?php

namespace Sientifica\Helpers\ShopifyAPI;
use \Sientifica\Helpers\Misc\Response;

/**
 * The responsability of this class is, to define the suitable method 
 * of product publication on shopify's platform.
 * It will depends on the available product data according to shopify's
 * API documentation: https://shopify.dev/api/admin-rest/2022-07/resources/product#post-products
 */

class ShopifyProductPublisher {

	private $productToPublish;
	private $product;
	private $shopifyApiHandler;


	public function __construct(\stdClass $product, \Sientifica\Helpers\ShopifyAPI\Shopify $shopifyApiHandler){
		$this->productToPublish = new \StdClass();
		$this->product = $product;
		$this->shopifyApiHandler = $shopifyApiHandler;
	}

	public function publish($isRealPublishing = true){
		$operationResponse = new Response();
		$this->prepareToPublishProduct();
		if ($isRealPublishing){
			$rawResponse = $this->shopifyApiHandler->createAProduct($this->productToPublish);
			return $rawResponse;
		}
		$operationResponse->value = 'Error';
		$operationResponse->notes = 'Mocking the publishing action.';
		$operationResponse->status = false;
		return $operationResponse;
	}



	public function prepareToPublishProduct(){
		$this->productToPublish->title = $this->product->title;
		$this->productToPublish->body_html = $this->product->body_html;
		$this->productToPublish->vendor = $this->product->vendor;
		$this->productToPublish->tags = $this->product->tags;
		$this->productToPublish->product_type = $this->product->product_type;
		$this->productToPublish->metafields = $this->product->metafields;
		$this->setVariantsToPublish();
		$this->setImages();
		return true;
	}


	public function setVariantsToPublish(){

		$options = array();
		if ($this->product->variants==""){//Product without variant definition
			return true;
		}


		if (is_array($this->product->variants) && $this->product->multioption){//Multioption variant

			$this->productToPublish->variants = array();
			foreach ($this->product->variants as $index => $variant){
				$optionsControl = 1;
				$toPublishVariant = new \stdClass();
				$toPublishVariant->barcode = $variant->upc;
				$toPublishVariant->price = "1.00";
				foreach ($variant->options as $option){
					if ($optionsControl==1){
						$toPublishVariant->option1 = $option->value." ".$option->type;
						$optionsControl++;
					}
					elseif ($optionsControl==2){
						$toPublishVariant->option2 = $option->value." ".$option->type;
						$optionsControl++;
					}
					elseif ($optionsControl==3){
						$toPublishVariant->option3 = $option->value." ".$option->type;
						$optionsControl++;
					}

					//Saves Options values
					if (!isset($options[$this->mapOptionType($option->type)])){
						$options[$this->mapOptionType($option->type)] = array($option->value." ".$option->type);
					}
					else{
						array_push($options[$this->mapOptionType($option->type)],$option->value." ".$option->type);	
					}
				}
				array_push($this->productToPublish->variants,$toPublishVariant);
			}

			//create the object for options
			$tmpOptionsToPublishArr = array();
			foreach ($options as $index => $optionValues){
				$optionToPublish = new \stdClass();
				$optionToPublish->name = $index;
				$optionToPublish->values = array();
				foreach ($optionValues as $optionValue){
					if (!in_array($optionValue,$optionToPublish->values))
						array_push($optionToPublish->values,$optionValue);
				}
				array_push($tmpOptionsToPublishArr,$optionToPublish);
			}
			$this->productToPublish->options = $tmpOptionsToPublishArr;
			return true;
		}


		//Single option variant product
		$this->productToPublish->variants = array();
		foreach ($this->product->variants as $index => $variant){

			$toPublishVariant = new \stdClass();
			$toPublishVariant->barcode = $variant->upc;
			$toPublishVariant->price = "1.00";
			$toPublishVariant->option1 = $variant->value." ".$variant->type;
			array_push($this->productToPublish->variants,$toPublishVariant);
		}
		return true;

	}


	public function setImages(){
		if (count($this->product->images)==0){
			return true;
		}

		$this->productToPublish->images = array();
		foreach ($this->product->images as $img){
			array_push($this->productToPublish->images,['src'=> $img]);
		}
		return true;
	}



	private function mapOptionType ($type){

		$typeToPresentation = [
			'Tabs.' => 'Units',
			'Caps.' => 'Units',
			'Gummies' => 'Units',
			'Loz.' => 'Units',
			'Chews.' => 'Units',
			'Gels.' => 'Units',
			'Fl. Oz.' => 'Net-Weight',
			'Oz.' => 'Net-Weight',
			'Lb.' => 'Net-Weight',
			'Lb.' => 'Net-Weight',
			'Mg.' => 'Mg',
		];
		if (isset($typeToPresentation[$type]))
			return $typeToPresentation[$type];
		return 'Units';

	}


	public function getToPublishProduct(){
		return $this->productToPublish;
	}




}