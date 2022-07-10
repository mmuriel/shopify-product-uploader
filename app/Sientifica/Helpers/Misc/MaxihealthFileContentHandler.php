<?php

namespace Sientifica\Helpers\Misc;


use \Sientifica\Helpers\Misc\interfaces\IFileContentHandler;
use \Sientifica\Helpers\ShopifyAPI\Shopify;

class MaxihealthFileContentHandler extends \Illuminate\Support\Collection implements IFileContentHandler {

	public function __construct(){
		parent::__construct();
	}

	public function processFile ($fileContent){
		$arrContent = preg_split("/\n/",$fileContent);
		foreach ($arrContent as $index=>$record){
			$productRaw = new \stdClass();
			$recordDataRaw = preg_split("/(\"){0,1};(\"){0,1}/",$record);

			if (count($recordDataRaw) == 17){
					$productRaw->title = $recordDataRaw[1];
					$productRaw->body_html = $recordDataRaw[5];
					$productRaw->vendor = 'Maxihealth';
					$productRaw->multioption = $this->isMultiOptionVariant($recordDataRaw[16]);
		
					$productRaw->tags = $this->processTags($recordDataRaw[14]);
					$productRaw->product_type = 'Vitamins & Supplements';
					$productRaw->variants = $this->processVariants($recordDataRaw[16]);
					if (is_array($productRaw->variants) && $productRaw->multioption == false){
						if ($productRaw->variants[0]->type=='' || $productRaw->variants[0]->value==''){
							$productRaw->variants = $this->processVariantsByAlternField($productRaw->variants,$recordDataRaw[14]);
						}
					}
					$productRaw->metafields = [
						[
							"key" => 'directions',
							"value" => $recordDataRaw[10],
							"type" => 'single_line_text_field',
							"namespace" => 'custom',
						],
						[
							"key" => 'short_description',
							"value" => $recordDataRaw[4],
							"type" => 'single_line_text_field',
							"namespace" => 'custom',
						],
						[
							"key" => 'subtitle',
							"value" => $recordDataRaw[2],
							"type" => 'single_line_text_field',
							"namespace" => 'descriptors',
						],
						[
							"key" => 'feature',
							"value" => $recordDataRaw[14],
							"type" => 'single_line_text_field',
							"namespace" => 'custom',
						],
						[
							"key" => 'other_ingredients',
							"value" => $recordDataRaw[12],
							"type" => 'multi_line_text_field',
							"namespace" => 'custom',
						],
						[
							"key" => 'suplement_facts',
							"value" => $recordDataRaw[11],
							"type" => 'multi_line_text_field',
							"namespace" => 'custom',
						],
					];

					$productRaw->images = $this->processImages([$recordDataRaw[6],$recordDataRaw[7],$recordDataRaw[8],$recordDataRaw[9]]);
					$this->push($productRaw);

					
				}
		}
	}

	public function processVariantsByAlternField($arrVariants,$alternVariantsDefinition){

		if ($alternVariantsDefinition == ''){
			return $arrVariants;
		}
		$alternVariantsDefinition = preg_replace("/(\(.+\)\ ?)/","",$alternVariantsDefinition);

		if (preg_match("/\,.+AND/i",$alternVariantsDefinition) == 1){ //3 or more elements

			$basicComponents = preg_split("/AND/i",$alternVariantsDefinition);
			$arrValues = preg_split("/\,\ */",$basicComponents[0]);
			$typeVar = '';
			if (preg_match("/([0-9\.]{1,6})\ +(.+)/i",$basicComponents[1],$matches)==1){
				array_push($arrValues,$matches[1]);
				$typeVar = $this->detectVariantType('('.trim($matches[2]).')');
			}

			if (count($arrValues) == count($arrVariants)){
				for ($i=0;$i<count($arrValues);$i++){
					$arrVariants[$i]->value = trim($arrValues[$i]);
					$arrVariants[$i]->type = $typeVar;
				}
			}

			return $arrVariants;

		}
		elseif (preg_match("/AND/i",$alternVariantsDefinition)==1){//2 elements

			$basicComponents = preg_split("/AND/i",$alternVariantsDefinition);
			$arrValues = array(trim($basicComponents[0]));
			$typeVar = '';
			if (preg_match("/([0-9\.]{1,6})\ +(.+)/i",$basicComponents[1],$matches)==1){
				array_push($arrValues,trim($matches[1]));
				$typeVar = $this->detectVariantType('('.trim($matches[2]).')');
			}

			if (count($arrValues) == count($arrVariants)){
				for ($i=0;$i<count($arrValues);$i++){
					$arrVariants[$i]->value = trim($arrValues[$i]);
					$arrVariants[$i]->type = $typeVar;
				}
			}			

			return $arrVariants;

		}
		elseif (preg_match("/([0-9\.]{1,5})\ ?([a-zA-Z\ \.\+\-])/",$alternVariantsDefinition)==1){//1 element
			preg_match("/([0-9\.]{1,6})\ +(.+)/i",$alternVariantsDefinition,$matches);
			$arrVariants[0]->value = trim($matches[1]);
			$arrVariants[0]->type = $this->detectVariantType('('.trim($matches[2]).')');
			return $arrVariants;
		}
		return $arrVariants;
	}

	public function processVariants($upcDefinitionCode){

		if ($upcDefinitionCode == '' || (preg_match("/753/",$upcDefinitionCode) == 0)){
			return "";
		}


		if ($this->isMultiOptionVariant($upcDefinitionCode)==1){

			$variants = $this->getMultioptionVariant($upcDefinitionCode);
			return $variants;

		}


		$arrVariantRawData = preg_split("/753/",$upcDefinitionCode);
		$variants = array();
		foreach ($arrVariantRawData as $variant){
			if ($variant != ''){
				$variant = preg_replace("/\"$/","",$variant);
				$tmpVariant = new \stdClass();
				$tmpVariant->multioption = false;
				$tmpVariant->rawData = trim("753".$variant);
				$tmpVariant->rawData = preg_replace("/(\ {1,4})/"," ",$tmpVariant->rawData);
				$tmpVariant->type = $this->detectVariantType($tmpVariant->rawData);
				$tmpVariant->value = $this->detectVariantValueByUPC($tmpVariant->rawData);
				$tmpVariant->upc = $this->detectVariantUPC($tmpVariant->rawData);

				array_push($variants,$tmpVariant);
			}
		}
		return $variants;
	}

	public function getMultioptionVariant($upcDefinitionCode){

		$arrVariantRawData = preg_split("/753/",$upcDefinitionCode);
		$variants = array();
		foreach ($arrVariantRawData as $variant){
			if ($variant != ''){
				$variant = preg_replace("/\"$/","",$variant);
				$tmpVariant = new \stdClass();
				$tmpVariant->multioption = true;
				$tmpVariant->rawData = trim("753".$variant);
				$tmpVariant->upc = $this->detectVariantUPC($tmpVariant->rawData);
				$tmpVariant->options = array();
				preg_match("/\(([^\(\)]+)\)/",$tmpVariant->rawData,$tmpPregMatch);
				//print_r($tmpPregMatch);
				$rawMultiOptionElements = preg_split("/\-/",$tmpPregMatch[1]);
				foreach ($rawMultiOptionElements as $rawOption){
					$tmpType = $this->detectVariantType ('('.trim($rawOption).')');
					$tmpValue = $this->detectVariantValueByUPC ('('.trim($rawOption).')');

					if ($tmpType!='' && $tmpValue!=''){
						$tmpOption = new \stdClass();
						$tmpOption->type = $tmpType;
						$tmpOption->value = $tmpValue;
						array_push($tmpVariant->options,$tmpOption);
					}
				}
				array_push($variants,$tmpVariant);
			}
		}
		return $variants;

	}

	public function detectVariantType ($variantString){

		if (preg_match("/\(([0-9\a-zA-Z\ ]{3,40})\)/",$variantString)==0){
			return "";
		}

		if (preg_match("/CAPS|Capsules/i",$variantString)==1){
			return 'Caps.';
		}

		if (preg_match("/CHEWS|Chewable Tablets/i",$variantString)){
			return 'Chews.';
		}

		if (preg_match("/TABS/i",$variantString)){
			return 'Tabs.';
		}

		if (preg_match("/LOZ/i",$variantString)){
			return 'Loz.';
		}

		if (preg_match("/FL OZ|Fl\. oz|fl\. oz./i",$variantString)){
			return 'Fl. Oz.';
		}

		if (preg_match("/OZ/i",$variantString)){
			return 'Oz.';
		}

		if (preg_match("/GELS/i",$variantString)){
			return 'Gels.';
		}

		if (preg_match("/LB/i",$variantString)){
			return 'Lb.';
		}

		if (preg_match("/Gummie/i",$variantString)){
			return 'Gummies';
		}

		if (preg_match("/mg|mg|mlg\./i",$variantString)){
			return 'Mg.';
		}
	}

	public function detectVariantValueByUPC ($variantString){

		if (preg_match("/\(([0-9\.]{1,6})\ /",$variantString,$results) == 1){
			return trim($results[1]);
		}
		return "";
	}

	public function detectVariantUPC($variantString){

		if (preg_match("/^753([0-9]{3,15})\ /",$variantString,$results) == 1){
			return trim($results[0]);
		}
		return "";
	}

	public function processTags ($tagsString){
		$tagsAsArray = preg_split("/\,/",$tagsString);
		return $tagsAsArray;
	}

	public function processImages (Array $linksToImages){

		$arrToReturn = array();
		for($i=0;$i<count($linksToImages);$i++){

			if ($linksToImages[$i] != '' && preg_match("/http/i",$linksToImages[$i])==1){
				$linksToImages[$i] = preg_replace("/dl=0/","dl=1",$linksToImages[$i]);
				$linksToImages[$i] = preg_replace("/www\.dropbox\.com/","dl.dropboxusercontent.com",$linksToImages[$i]);
				array_push($arrToReturn,trim($linksToImages[$i]));
			}
		}

		return $arrToReturn;
	}


	public function isMultiOptionVariant ($rawStringUpcDefition){

		if (preg_match("/(\([^><\)\(]+\-{1,3}[^><\)\(]+\))/",$rawStringUpcDefition,$matches)==1){
			return true;
		}
		return false;

	}


}