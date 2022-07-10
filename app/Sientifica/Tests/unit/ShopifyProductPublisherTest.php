<?php

namespace Sientifica\Tests\unit;

use Illuminate\Foundation\Testing\TestCase ;
use Illuminate\Contracts\Console\Kernel;


use Sientifica\Helpers\ShopifyAPI\Shopify;
use Sientifica\Helpers\ShopifyAPI\ShopifyProductPublisher;
use Sientifica\Helpers\Misc\MaxihealthFileContentNormalizer;
use Sientifica\Helpers\Misc\MaxihealthFileContentHandler;

class ShopifyProductPublisherTest extends TestCase {

    private $normalizedContent;
    private $contentHandler;
    private $spClient;

	public function setUp(){
        parent::setUp();
        $contentNormalizer = new MaxihealthFileContentNormalizer();
        $this->normalizedContent = $contentNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
        $this->contentHandler = new MaxihealthFileContentHandler();
        $this->contentHandler->processFile($this->normalizedContent);
        $this->spClient = new Shopify(env('SHPFY_BASEURL'),env('SHPFY_ACCESSTOKEN'));
    }

    /**
    * Testing with record #93 in test csv file (Mel-O-Chew™)
    */
    public function testValidatesVariantsForMultioptionVariantsProduct(){

        //print_r($this->contentHandler->get(93));
        $product = $this->contentHandler->get(93);
        //print_r($product->variants);
        $productPublisher = new ShopifyProductPublisher($this->contentHandler->get(93),$this->spClient);
        $productPublisher->setVariantsToPublish();
        $toPublishProduct = $productPublisher->getToPublishProduct();

        $this->assertEquals(2,count($toPublishProduct->options),"This product must have to options variants: Mg, Units");
        $this->assertEquals('Mg',$toPublishProduct->options[0]->name,"The first product option name must: Mg");
        $this->assertEquals('100 Chews.',$toPublishProduct->variants[0]->option2,"For the first product variant, its option1 attibute must be: 100 Chews.");


    }
 

    /**
    * Testing with record #77 in test csv file (Maxi Health Supreme®)
    */
    public function testValidatesVariantsForUniqueOptionVariantsProduct(){

        $product = $this->contentHandler->get(77);
        $productPublisher = new ShopifyProductPublisher($this->contentHandler->get(77),$this->spClient);
        $productPublisher->setVariantsToPublish();
        $toPublishProduct = $productPublisher->getToPublishProduct();
        $this->assertEquals(4,count($toPublishProduct->variants),"This product must have 4 variants");
        $this->assertEquals('360 Tabs.',$toPublishProduct->variants[3]->option1,"Last variant, option1 attribute must be: 360 Tabs.");
    }



    /**
    * Testing with records #1 to #10 in test csv file (Maxi Health Supreme®)
    */
    public function testMockingToPublishAProduct(){

        for($i=1;$i<=10;$i++){
            $product = $this->contentHandler->get($i);
            $productPublisher = new ShopifyProductPublisher($this->contentHandler->get($i),$this->spClient);
            $operationResponse = $productPublisher->publish(false);
            $toPublishProduct = $productPublisher->getToPublishProduct();
            echo "\n------------------------------------------\n";
            echo "Loading product: ".$toPublishProduct->title."\n";
            echo "------------------------------------------\n";
            echo "Request Object:\n";
            print_r($toPublishProduct);
            echo "\n\nResponse Object:\n";
            print_r($operationResponse);
        }
        /*
        $this->assertEquals(4,count($toPublishProduct->variants),"This product must have 4 variants");
        $this->assertEquals('360 Tabs.',$toPublishProduct->variants[3]->option1,"Last variant, option1 attribute must be: 360 Tabs.");
        */
    }


    /* Preparing the Test */
	public function createApplication(){
        $app = require __DIR__.'/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

}