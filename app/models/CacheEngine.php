<?php

namespace App\Models;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\JS;
class CacheEngine {

	private $config;

	private $sassInDirectory;
	private $sassOutDirectory;

	private $javascriptInFileName;
	private $javascriptOutFileName;
	private $logger;

	private $workspacePaths = [];

	private $ignoredFiles = [];
	private $oneTimeBuildFiles = [];
	private $toBeCompressed = [];

	private $cacheEngineStarted = false;

	public function __construct($logger) {
		$this->logger = $logger;
	}

	public function setSassInDirectory($sassInDirectory) {
		$this->sassInDirectory = $sassInDirectory;
	}

	public function setSassOutDirectory($sassOutDirectory) {
		$this->sassOutDirectory = $sassOutDirectory;
	}

	public function setJavascriptInFile($javascriptInFileName) {
		$this->javascriptInFileName = $javascriptInFileName;
	}

	public function setJavascriptOutFile($javascriptOutFileName) {
		$this->javascriptOutFileName = $javascriptOutFileName;
	}

	/**
	 * Build these files one time if they do not exist already
	 * These files are not expected to change often
	 * They will be built next run if they are deleted
	 */
	public function setOneTimeBuildFiles($files) {
		if (!$this->cacheEngineStarted) {
			$this->oneTimeBuildFiles = $files;
		}
	}

	/**
	 * Compiled 
	 * @param [type] $files [description]
	 */
	public function setToBeCompressed($files) {
		if (!$this->cacheEngineStarted) {
			$this->toBeCompressed = $files;
		}
	}

	/**
	 * Search backwards starting from haystack length characters from the end
	 */
	private function startsWith($haystack, $needle) {
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	/**
	 * Create each directory if it does not exist
	 * and use 0777 for the directories
	 * @param  Array $paths Paths to check or create
	 */
	private function directoryCheck($paths) {
		foreach ($paths as $path) {
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
		}
	}
	
	/**
	 * Compile and minify the javascript files
	 * within the same directory they reside in
	 */
	private function compileJavascriptFiles() {
		$minifier = new JS($this->javascriptInFileName);
		$minifier->minify($this->javascriptOutFileName);
	}

	private function compileSassFiles() {
		$scss = new Compiler;
		$cacheLogger = $this->logger;
		$cacheLogger->info('Checking OTB files');
		
		// each file in sass directory
		foreach(new \DirectoryIterator($this->sassInDirectory) as $file) {
			if ($file->isDot()) continue;
			if ($file->getExtension() !== 'scss') continue;
			if ($this->startsWith($file->getBasename('.scss'), '_')) continue;

			// if file is in the onetime build array
			if (in_array($file->getFilename(), $this->oneTimeBuildFiles)) {
				$cssFileName = str_replace('.scss', '.css', $file->getFilename());

				// if file already exists
				$cacheLogger->info("OTB file ({$file->getFilename()}) already exists, skipping");
				if (file_exists($this->sassOutDirectory . '/' . $cssFileName)) continue;
			}

			// input data
			$in = file_get_contents($this->sassInDirectory . '/' . $file->getFilename());

			// compile and compress data
			$out = $scss->compile($in);

			// put into the file
			file_put_contents($this->sassOutDirectory . '/' . $file->getBasename('.scss') . '.css', $out);
			$cacheLogger->info("Success, built ({$file->getFilename()})");
		}
	}

	public function build() {
		$this->cacheEngineStarted = true;

		$this->workspacePaths[] = $this->sassInDirectory;
		$this->workspacePaths[] = $this->sassOutDirectory;

		$this->directoryCheck($this->workspacePaths);
		$this->compileJavascriptFiles();
		$this->compileSassFiles();
	}
}