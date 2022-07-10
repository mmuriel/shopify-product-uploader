<?php

namespace Sientifica\Tests\unit;

use Illuminate\Foundation\Testing\TestCase ;
use Illuminate\Contracts\Console\Kernel;


use \Sientifica\Helpers\Misc\MaxihealthFileContentHandler;
use \Sientifica\Helpers\Misc\MaxihealthFileContentNormalizer;

class MaxihealthFileContentHandlerTest extends TestCase {

    private $normalizedContent;

	public function setUp(){
        parent::setUp();
        $contentNormalizer = new MaxihealthFileContentNormalizer();
        $this->normalizedContent = $contentNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
    }
 

    /**
     * 
     * 
     */
	public function testLastRecordInFileHasNoSubtitle(){
        $contentHandler = new MaxihealthFileContentHandler();
        $contentHandler->processFile($this->normalizedContent);
        $this->assertEquals("",$contentHandler->last()->metafields[2]['value']);

	}

    /**
    * Record #204 in test csv file (Maxi D 2000 Liquid Gels™)
    * 
    * UPC field: "753406344185  (180 GELS) CD2 CD22  Chew D"
    * 
    */

    public function testSelectVariantTypeAndValueFromUpcFieldWith1Variants(){
        $contentHandler = new MaxihealthFileContentHandler();

        $upcDefinitionCode = '753406344185  (180 GELS) CD2 CD22  Chew D';
        $variants = $contentHandler->processVariants($upcDefinitionCode);

        $this->assertEquals(180,$variants[0]->value,"variant->value must be 90");

        $this->assertEquals('Gels.',$variants[0]->type,"variant->type must be: 'Gels.'");


    }


    /**
    * Record #191 in test csv file (Yummie C 250™ (Cherry Flavor))
    * 
    * UPC field: "753406168095  (90 CHEWS)753406168187  (180 CHEWS)"
    * 
    */

    public function testSelectVariantTypeAndValueFromUpcFieldWith2Variants(){
        $contentHandler = new MaxihealthFileContentHandler();

        $upcDefinitionCode = '753406168095  (90 CHEWS)753406168187  (180 CHEWS)';
        $variants = $contentHandler->processVariants($upcDefinitionCode);

        $this->assertEquals(90,$variants[0]->value,"variant->value must be 90");
        $this->assertEquals(180,$variants[1]->value,"variant->value must be 180");

        $this->assertEquals('Chews.',$variants[0]->type,"variant->type must be: 'Chews.'");


    }


    /**
    * Record #12 in test csv file (B-12 Lozenges™)
    * 
    * UPC field: "753406008094  (90 LOZ)753406008186  (180 LOZ)753406008360  (360 LOZ)"
    * 
    */

    public function testSelectVariantTypeAndValueFromUpcFieldWith3Variants(){
        $contentHandler = new MaxihealthFileContentHandler();

        $upcDefinitionCode = '753406008094  (90 LOZ)753406008186  (180 LOZ)753406008360  (360 LOZ)';
        $variants = $contentHandler->processVariants($upcDefinitionCode);

        $this->assertEquals(90,$variants[0]->value,"variant->value must be 90");
        $this->assertEquals('753406008094',$variants[0]->upc,"variant->upc must be 753406008094");

        $this->assertEquals(180,$variants[1]->value,"variant->value must be 180");
        $this->assertEquals(360,$variants[2]->value,"variant->value must be 180");

        $this->assertEquals('Loz.',$variants[0]->type,"variant->type must be: 'Loz.'");


    }




    /**
    * Record #71 in test csv file
    * 
    * UPC field: "753406173099  (90 CAPS)753406173181  (180 CAPS)753406173365  (360 CAPS)"
    * Size and Form field: 90, 180 and 360 Capsules
    */

    public function testSelectVariantTypeAndValueFromNonUpcFieldWith3Variants(){
        $contentHandler = new MaxihealthFileContentHandler();
        $variant_1 = new \stdClass();
        $variant_1->upc = 753406173099;
        $variant_1->type = '';
        $variant_1->value = '';

        $variant_2 = new \stdClass();
        $variant_2->upc = 753406173181;
        $variant_2->type = '';
        $variant_2->value = '';

        $variant_3 = new \stdClass();
        $variant_3->upc = 753406173365;
        $variant_3->type = '';
        $variant_3->value = '';

        $sizeAndForm = '90, 180 and 360 Capsules';
        $variants = array($variant_1,$variant_2,$variant_3);
        $variants = $contentHandler->processVariantsByAlternField($variants,$sizeAndForm);

        $this->assertEquals(90,$variants[0]->value,"variant->value must be 90");
        $this->assertEquals(180,$variants[1]->value,"variant->value must be 180");
        $this->assertEquals(360,$variants[2]->value,"variant->value must be 360");

        $this->assertEquals('Caps.',$variants[0]->type,"variant->type must be: 'Caps.'");


    }


    /**
    * Record #86 in test csv file (Maxi Omega 3 Concentrate™)
    * 
    * UPC field: "753406215096  (90 GELS)753406215188  (190 GELS)"
    * Size and Form field: 90 and 190 (180 + 10 Free) Enteric Coated Capsules
    */

    public function testSelectVariantTypeAndValueFromNonUpcFieldWith2Variants(){
        $contentHandler = new MaxihealthFileContentHandler();
        $variant_1 = new \stdClass();
        $variant_1->upc = 753406215096;
        $variant_1->type = '';
        $variant_1->value = '';

        $variant_2 = new \stdClass();
        $variant_2->upc = 753406215188;
        $variant_2->type = '';
        $variant_2->value = '';

        $sizeAndForm = '90 and 190 (180 + 10 Free) Enteric Coated Capsules';
        $variants = array($variant_1,$variant_2);
        $variants = $contentHandler->processVariantsByAlternField($variants,$sizeAndForm);

        $this->assertEquals(90,$variants[0]->value,"variant->value must be 90");
        $this->assertEquals(190,$variants[1]->value,"variant->value must be 190");

        $this->assertEquals('Caps.',$variants[0]->type,"variant->type must be: 'Caps.'");
    }


    /**
    * Record #201 in test csv file (Naturemax Energize™)
    * 
    * UPC field: "753406138012  (1 LB)"
    * Size and Form field: 1.17 lb. Powder
    */

    public function testSelectVariantTypeAndValueFromNonUpcFieldWith1Variants(){
        $contentHandler = new MaxihealthFileContentHandler();
        $variant_1 = new \stdClass();
        $variant_1->upc = 753406138012;
        $variant_1->type = '';
        $variant_1->value = '';

        $sizeAndForm = '1.17 lb. Powder';
        $variants = array($variant_1);
        $variants = $contentHandler->processVariantsByAlternField($variants,$sizeAndForm);

        $this->assertEquals('1.17',$variants[0]->value,"variant->value must be 1.17");
        $this->assertEquals('Lb.',$variants[0]->type,"variant->type must be: 'Lb.'");
    }    


    /**
    * Record #12 in test csv file (B-12 Lozenges™)
    * 
    * Image records, columns: G, H, I, J:
    * 
    * - Image URL 1: https://www.dropbox.com/s/dflyu4w4w26d7vb/B129%20MAIN.png?dl=0
    * - Image URL 2: https://www.dropbox.com/s/u20lduq032vdg76/B12%20MAIN.png?dl=0
    * - Image URL 3: https://www.dropbox.com/s/i7f3et5fzbyhlad/B123%20MAIN.png?dl=0
    * - IMage URL 4: (Empty)
    */

    public function testGetImageUrlsFromCSVFile(){
        $contentHandler = new MaxihealthFileContentHandler();
        $image_url_1 = 'https://www.dropbox.com/s/dflyu4w4w26d7vb/B129%20MAIN.png?dl=0';
        $image_url_2 = 'https://www.dropbox.com/s/u20lduq032vdg76/B12%20MAIN.png?dl=0';
        $image_url_3 = 'https://www.dropbox.com/s/i7f3et5fzbyhlad/B123%20MAIN.png?dl=0';
        $image_url_4 = '';

        $processedImgs = $contentHandler->processImages([$image_url_1,$image_url_2,$image_url_3,$image_url_4]);

        $this->assertEquals(3,count($processedImgs),"There are more than 3 image links in array");
        $this->assertRegExp("/dl=1$/",$processedImgs[0]);
    }


    /**
    * Record #94 in test csv file (Mel-O-Chew™)
    */

    public function testGetVariantsOnChallengingRecordFromUpcDefinition(){
        $contentHandler = new MaxihealthFileContentHandler();        
        $upcFieldRawData = '753406232109  (1 mg  - 100 CHEWS) 753406232208  (1 mg - 200 CHEWS)753406363100  (3 mg - 100 CHEWS)753406363209 (3 mg - 200 CHEWS)753406362103  (5 mg  - 100 CHEWS)753406362202  (5 mg - 200 CHEWS)';
        
        $variants = $contentHandler->processVariants($upcFieldRawData);

        $this->assertEquals(6,count($variants),"Total variants for UPC definition given, must be 6");

    }


    /**
    * Record #94 in test csv file (Mel-O-Chew™)
    */

    public function testAnUpcDefitionWithHyphensIndicatesAMultioptionVariants(){
        $contentHandler = new MaxihealthFileContentHandler();
        $upcFieldRawData = '753406232109  (1 mg  - 100 CHEWS) 753406232208  (1 mg - 200 CHEWS)753406363100  (3 mg - 100 CHEWS)753406363209 (3 mg - 200 CHEWS)753406362103  (5 mg  - 100 CHEWS)753406362202  (5 mg - 200 CHEWS)';
        
        $this->assertTrue($contentHandler->isMultiOptionVariant($upcFieldRawData),"A multioption variant does have hyphens in UPC definition, this string doesn't have hyphens");

    }


    /**
    * Record #94 in test csv file (Mel-O-Chew™)
    */

    public function testProcessingAHyphenedUpcDefinitionToMultioptionVariants(){
        $contentHandler = new MaxihealthFileContentHandler();
        $upcFieldRawData = '753406232109  (1 mg  - 100 CHEWS) 753406232208  (1 mg - 200 CHEWS)753406363100  (3 mg - 100 CHEWS)753406363209 (3 mg - 200 CHEWS)753406362103  (5 mg  - 100 CHEWS)753406362202  (5 mg - 200 CHEWS)';
        
        $multiOptionVariants = $contentHandler->getMultioptionVariant($upcFieldRawData);
        $this->assertEquals(6,count($multiOptionVariants),"Total variants for UPC definition given, must be 6");
        $this->assertEquals("200",$multiOptionVariants[5]->options[1]->value,"Value of second option, last variant, must be 200");


    }


    /* Preparing the Test */
	public function createApplication(){
        $app = require __DIR__.'/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

}