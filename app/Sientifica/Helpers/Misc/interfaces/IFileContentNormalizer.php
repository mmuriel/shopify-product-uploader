<?php

namespace Sientifica\Helpers\Misc\interfaces;

/**
 * This interface defines a contract to define a method that helps
 * the normalization of any data source file.
 * 
 * 
 */

interface IFileContentNormalizer{

	/**
	 * The method that must implemented to normalize any file
	 * 
	 * @param string $filePath	The relative or full path to file the that must be normalized
	 * @return string $normalizedFileContent	The content of the file normalized, in memory data.
	 * 
	 */
	public function normalizeFileContent ($filePath);

}