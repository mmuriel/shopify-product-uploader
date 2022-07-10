<?php

namespace Sientifica\Helpers\Misc\interfaces;

/**
 * This interface defines the necessary methods to handle
 * the content of the any datasource
 * 
 * 
 */

interface IFileContentHandler{

	/**
	 * The method that must be implemented to load to memory the data from data source.
	 * 
	 * @param string $contentFile	Normalized file content.
	 * @return Sientifica\Helpers\Misc\Response $response	Response object with info about the load data to memory process.
	 * 
	 */
	public function processFile ($fileContent);

}