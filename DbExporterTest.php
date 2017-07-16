<?php 

	require_once "DbExporter.php";

	$dbExporter = DbExporter::getExporter(); 
	$dbExporter->exportData();

?>