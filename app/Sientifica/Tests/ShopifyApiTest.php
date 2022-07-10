<?php

namespace Sientifica\Tests;

use Illuminate\Foundation\Testing\TestCase ;
use Illuminate\Contracts\Console\Kernel;
use Sientifica\Models\Shopify\Product;
use Sientifica\Models\Shopify\Variant;


use Sientifica\Helpers\ShopifyAPI\Shopify;

class ShopifyApiTest extends TestCase {

	public $prd,$var1,$var2;

	public function setUp()
    {
        parent::setUp();
        $this->prepareForTests();

        $this->prd = new Product();
		$this->prd->idsp = "shpfy_890987645";
		$this->prd->title = 'Colombian Sleeve Yellow';
		$this->prd->vendor = 'Sientifica';
		$this->prd->product_type = 'Sleeve';
		$this->prd->handle = 'colombian-sleeve-yellow';
		$this->prd->save();

		$this->var1 = new Variant();
		$this->var1->idsp = "shpfy_5678890951";
		$this->var1->sku = 'SL-COL-Y-L';
		$this->var1->title = 'Large';
		$this->var1->idproduct = $this->prd->id;
		$this->var1->price = 12.50;
		$this->var1->save();

		$this->var2 = new Variant();
		$this->var2->idsp = "shpfy_5678890952";
		$this->var2->sku = 'SL-COL-Y-XL';
		$this->var2->title = 'XL';
		$this->var2->idproduct = $this->prd->id;
		$this->var2->price = 12.50;
		$this->var2->save();
    }
 

	public function testInmemoryDatabaseAddingRecords(){		

		/* Testing saved items to database */
		$this->assertDatabaseHas('products',['idsp' => 'shpfy_890987645','title' => 'Colombian Sleeve Yellow']);
		$this->assertDatabaseHas('variants',['idsp' => 'shpfy_5678890951','idsp' => 'shpfy_5678890952']);

		
	}

	public function testInmemoryDatabaseProductVariantsRelationship(){

		$tmpPrd = Product::where('title','=','Colombian Sleeve Yellow')->first();
		$this->assertEquals('Sientifica',$this->var1->product->vendor);
		$this->assertEquals('SL-COL-Y-L',$tmpPrd->variants[0]->sku);


	}


	
	public function testGetProductsFromApi(){

		//$spClient = new Shopify('f7adb74791e9b142c7f6bc3a64bcc3b0','5486391dc27e857cfc1e8986b8094c12','Sientifica-2.myshopify.com/admin/api/2020-01/');
		$spClient = new Shopify(env('SHPFY_BASEURL'),env('SHPFY_ACCESSTOKEN'));
		$options = "ids=7698337988823,7698334744791";
		$data = $spClient->getAllProducts($options);

		$this->assertContains("Cal Max",$data->products[0]->title,"The name of the product is not Cal Max™".$data->products[0]->title);
		$this->assertEquals(2,count($data->products),"The quantity is not equals to: 2");
	}


	public function testGetVariantsFromApi(){

		$spClient = new Shopify(env('SHPFY_BASEURL'),env('SHPFY_ACCESSTOKEN'));
		$options = "ids=7698337988823,7698334744791";
		$data = $spClient->getAllProducts($options);
		$variantRaw = $spClient->getSingleProductVariant($data->products[0]->variants[0]->id);
		$this->assertEquals('753406010097',$variantRaw->variant->barcode);

	}



	public function testGetSingleProductFromApi(){

		$spClient = new Shopify(env('SHPFY_BASEURL'),env('SHPFY_ACCESSTOKEN'));
		$data = $spClient->getSingleProduct('7698334744791');
		$this->assertRegExp('/^Focus\ Max\ Two/i',$data->product->title);

	}


	public function testCreateAProductOverAPI(){

		$spClient = new Shopify(env('SHPFY_BASEURL'),env('SHPFY_ACCESSTOKEN'));
		$product = new \stdClass();
		$product->title = 'Maxi Lipoic Supreme™';
		$product->body_html = 'Involved in blood sugar regulation. Unique combination of Lipoic Acid, Choline and Inositol. <p>Maxi-Health Research&reg; proudly offers you Maxi Lipoic Supreme&trade;, which combines the universal antioxidant alpha lipoic acid, inositol, and choline.</p><p>Alpha lipoic acid gives you antioxidant and energy enhancer support. It is better than vitamin E at inhibiting the creation of free radicals and protein oxidation. It also supports maintenance of normal blood glucose levels. Alpha lipoic enhances the production of glutathione, which supports exposure to excessive stress and toxic substances.</p> <p>We added inositol, which supports mood, the cardiovascular system, and immunity.</p> <p>Choline is added to support liver function, production of acetylcholine (an important neurotransmitter), and for brain development</p><p>Try Maxi Lipoic Supreme&trade; today.</p>
			<h2>Directions</h2><p>Take two (2) Maxicaps&trade; once or twice daily with meals, or as directed.</p><p>Vegetable cellulose, microcrystalline cellulose, magnesium stearate, Enzymax&reg;&nbsp;(calcium carbonate,&nbsp;bromelain, papain, lipase, amylase, protease, silica), chlorophyll.</p><p>This product contains <strong>NO</strong> animal products, soy, wheat, salt, sugar, milk, yeast, gluten, artificial flavors, colorings or preservatives.</p><p>Enzymax&reg;, a vital digestive enzyme complex, is a registered trademark of Maxi Health Research&reg; LLC.</p>';
		$product->tags = ['Gluten Free','Vegetarian'];
		$product->vendor = 'Maxihealth';
		$product->product_type = 'Vitamins & Supplements';


		//$newShopifyProduct = $spClient->createAProduct($product);
		//print_r($newShopifyProduct);

	}



	/* Preparing the Test */

	public function createApplication()
    {
        $app = require __DIR__.'/../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

     /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     */
    private function prepareForTests()
    {

     	\Artisan::call('migrate');
    }

}