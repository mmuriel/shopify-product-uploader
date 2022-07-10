<?php

namespace Sientifica\Helpers\Misc;

use \Sientifica\Helpers\Misc\interfaces\IFileContentNormalizer;

class MaxihealthFileContentNormalizer implements IFileContentNormalizer{


	public function normalizeFileContent ($filePath){

		$fo = fopen($filePath,"r");
		$fileContent = '';
		while ($buffer = fgets($fo)){
			$fileContent .= $buffer;
		}
		fclose($fo);
		$fileContent = $this->cleanUselessNewLineCharacters($fileContent);
		$fileContent = $this->cleanInlineCSSCode($fileContent);
		$fileContent = $this->cleanHTMLEntities($fileContent);
		$fileContent = $this->cleanSemicolonInsideHTMLCode ($fileContent);
		return $fileContent;

	}

	/**
	 * This function removes any unnecessary new line chars, 
	 * it makes clean every record of the csv file, ready to be
	 * read line by line.
	 * 
	 * @param string $fileContent 	Raw file csv content.
	 * @return string $fileContent 	Cleaned file content, every record in one file row.
	 */

	private function cleanUselessNewLineCharacters($fileContent){

		$fileContent = preg_replace("/>([\ \	]){0,100}([\r\n|\n|\r]{1,3})([\ \	]){0,100}/",'>',$fileContent);
		$fileContent = preg_replace("/(\ ){0,100}([\r\n|\n|\r]{1,3})(\ ){0,100}</",'<',$fileContent);
		$fileContent = preg_replace("/(\r\n|\n|\r)([0-9]{1,4});/","----$2;",$fileContent);
		$fileContent = preg_replace("/(\r\n|\n|\r)/","",$fileContent);
		$fileContent = preg_replace("/\-\-\-\-/","\n",$fileContent);

		return $fileContent;

	}


	/**
	 * This function removes any inline css definition because of semicolons, 
	 * 
	 * 
	 * @param string $fileContent 	Raw file csv content.
	 * @return string $fileContent 	Cleared of CSS inline definitions
	 */

	private function cleanInlineCSSCode($fileContent){

		$fileContent = preg_replace("/style=\"{1,2}([^\"><]){0,400}\"{1,2}/","",$fileContent);
		return $fileContent;

	}


	/**
	 * This function removes any HTML entities definition because of semicolons, 
	 * 
	 * 
	 * @param string $fileContent 	Raw file csv content.
	 * @return string $fileContent 	Cleared of HTML entities
	 */

	private function cleanHTMLEntities($fileContent){

		$fileContent = html_entity_decode($fileContent,ENT_QUOTES);
		return $fileContent;

	}


	/**
	 * 
	 * This function looks for semicolons inside HTML in fields: 
	 * Long Description, Directions, Suplement Facts, Other Ingredients snd Review
	 * 
	 * @param string $fileContent 	Raw file csv content.
	 * @return string $fileContent 	Cleared of semicolons in HTML code
	 * 
	 */
	private function cleanSemicolonInsideHTMLCode ($fileContent){
		$fileContent = preg_replace("/>([^><;\"]{1,1400})(;)([^><;\"]{1,1400})</",">$1. $3<",$fileContent);
		$arrContent = preg_split("/\n/",$fileContent);
		$tmpFileContent = '';
		foreach ($arrContent as $index=>$record){
			$recordDataRaw = preg_split("/(\"){0,1};(\"){0,1}/",$record);
			if (count($recordDataRaw)>17){
				$record = preg_replace("/; /",". ",$record);

			}
			if ($tmpFileContent == '')
				$tmpFileContent = $record;
			else
				$tmpFileContent = $tmpFileContent."\n".$record;
		}
		$fileContent = $tmpFileContent;
		return $fileContent;		
	}

}