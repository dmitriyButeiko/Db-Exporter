<?php 

	$currentWordkedDirectory = getcwd();
	include_once $currentWordkedDirectory . "/../DbExporter.php";
 
	class DbExporterTest extends PHPUnit_Framework_TestCase
	{
		private $dbExporter;

		public function __construct()
		{ 
		}

		public function test_constructor()
		{
			$this->dbExporter = DbExporter::getExporter(); 
		}
	}
?>