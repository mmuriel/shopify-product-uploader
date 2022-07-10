<?php

namespace Sientifica\Tests\unit;

use Illuminate\Foundation\Testing\TestCase ;
use Illuminate\Contracts\Console\Kernel;


use Sientifica\Helpers\Misc\MaxihealthFileContentNormalizer;

class MaxihealthFileContentNormalizerTest extends TestCase {

	public function setUp(){
        parent::setUp();
    }
 

    /**
     * Raw content file has a lot of unnecessary new line characters
     * that makes unreadable the file.
     * 
     * This test validates that after the "clean" process, every record
     * of the content file fits in just one row.
     * 
     */
	public function testUnNormalizedFileHasMoreLinesThanNormalizedOne(){		

		$maxihealthFileNormalizer = new MaxihealthFileContentNormalizer();
		$file = file(base_path()."/docs/product_data-2022-07-06_15-22-27.csv");
		$this->assertEquals(23688,count($file)); //Original file has 23688 lines
		$normalizedContent = $maxihealthFileNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
		$normalizedContentArray = preg_split("/\\n/",$normalizedContent);
		$this->assertEquals(250,count($normalizedContentArray));//normalized files has 250 lines
	}

    /**
    * 
    * No css definitions are permitted because of semicolon chars. Semicolons are key char to 
    * separate fields in csv format.
    * 
    */

    public function testARecordMustNotHaveCSSDefinitions(){        

        $maxihealthFileNormalizer = new MaxihealthFileContentNormalizer();
        $normalizedContent = $maxihealthFileNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
        $normalizedContentArray = preg_split("/\\n/",$normalizedContent);

        $regexpTest = preg_match("/border\-collapse:collapse;\ height:88px;/",$normalizedContentArray[246]);

        //echo $normalizedContentArray[246];
        $this->assertEquals(0,$regexpTest);
    }


    /**
    * 
    * No htmlentities are allowed because of semicolon chars. Semicolons are key char to 
    * separate fields in csv format.
    * 
    */

    public function testARecordMustNotHaveHTMLEntities(){        

        $maxihealthFileNormalizer = new MaxihealthFileContentNormalizer();
        $normalizedContent = $maxihealthFileNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
        $normalizedContentArray = preg_split("/\\n/",$normalizedContent);

        $regexpTest = preg_match("/&reg;/",$normalizedContentArray[221]);
        $this->assertEquals(0,$regexpTest);//
    }


    /**
    * 
    * Every record must have 17 fields only.
    * This test must assert OK with next sum: 250(records) * 17(fields) = 4250 (total fields) in file. 
    * 
    * Note: There are 9 records that is almost impossible to get, 
    * because semicolon character show up, doesn't follow any pattern.
    * 
    */

    public function testEveryRecordMustHave17FieldsOnly(){        

        $maxihealthFileNormalizer = new MaxihealthFileContentNormalizer();
        $normalizedContent = $maxihealthFileNormalizer->normalizeFileContent(base_path()."/app/Sientifica/Docs/product_data-2022-07-06_15-22-27.csv");
        $normalizedContentArray = preg_split("/\\n/",$normalizedContent);
        $counter = 0;
        $countFails = 0;
        foreach ($normalizedContentArray as $index => $record){
            $recordDataRaw = preg_split("/(\"){0,1};(\"){0,1}/",$record);
            if (count($recordDataRaw)>17){
                /*
                echo $index.": ".count($recordDataRaw)."\n";
                echo $record."\n";
                echo "---------------------\n";
                */
                $countFails++;
            }
            else {
                /*
                echo $index.": ".count($recordDataRaw).", Ok!\n";
                */
                $counter += (integer)(count($recordDataRaw));
            }
            
        }
        $this->assertEquals(4250,$counter);
        
    }

    /* Preparing the Test */
	public function createApplication(){
        $app = require __DIR__.'/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

}