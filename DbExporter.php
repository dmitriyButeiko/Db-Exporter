<?php


/**
 *
 */
class DbExporter
{

    private $backupPath = "backup";
    private $lastUpdateTimeFileName = "latestBackup.txt";
    private $backupTimeout = 1;
    private $backupsAmount = 3;
    private $access;

    /**
    *
    */
    public function __construct()
    {
        $this->createInitialFiles();
        $this->access = array(
            array(
                "host" => "localhost",
                "userName" => "root",
                "userPassword" => "",
                "databaseName" => "testdatabasemyadmin"
            )
           /* array(
                "host" => "bjk.mysql.ukraine.com.ua",
                "userName" => "bjk_comet",
                "userPassword" => "8v6ddlzs",
                "databaseName" => "bjk_comet"
            )*/
        );
    }


    public function getExporter()
    {
        static $instance = null;
        if($instance == null)
        {
            $instance = new DbExporter();
        }       
        return $instance;
    }       

    public function createInitialFiles()
    {
        if(!file_exists($this->backupPath))
        {
            mkdir($this->backupPath);
        }

        if(!file_exists($this->backupPath . "/" . $this->lastUpdateTimeFileName))
        {
            $data = time();
            file_put_contents($this->backupPath . "/" . $this->lastUpdateTimeFileName, $data);
        }
    }

    /**
     * @param void $dataInfo array(
     *
     */
    public function exportData()
    {
        $backupNeeded = $this->isNewBackupNeeded($this->backupTimeout);

		var_dump($backupNeeded);
		
        if($backupNeeded)
        {
            $this->deletePreviousBackups();
            foreach($this->access as $singleAccess)
            {
                $currentDate = date("d_m_Y");
                $accessFileName = $singleAccess["databaseName"] . "_" . $currentDate . ".sql";
                $this->backupTables($singleAccess["host"], $singleAccess["userName"], $singleAccess["userPassword"], $singleAccess["databaseName"], false,$this->backupPath . "/" . $accessFileName);
            }

            $data = time();
            file_put_contents($this->backupPath . "/" . $this->lastUpdateTimeFileName, $data);
        }
    }

    /**
     *
     */
    public function getFileNameByCurrentData()
    {
        // TODO: implement here
    }

    /**
     *
     */
    public function deletePreviousBackups()
    {
        $databases = array();

        foreach($this->access as $singleAccess)
        {
            $singleDatabaseName = $singleAccess["databaseName"];
            $databases[$singleDatabaseName] = 0;
        }

        $files = glob($this->backupPath . "/" . '*'); // get all file names
        foreach($files as $file){ // iterate files

            $fileDatabaseName = $this->getDatabaseFromFileName($file);

            if(array_key_exists($fileDatabaseName, $databases))
            {
                $databases[$fileDatabaseName] = $databases[$fileDatabaseName] + 1;

                if($databases[$fileDatabaseName] > $this->backupsAmount)
                {
                    $this->deleteTheOldestFile($files, $fileDatabaseName);
                }

                $this->deleteFile($file);
            }
        }
    }

    private function deleteTheOldestFile($files, $fileDatabaseName)
    {

    }

    private function getDatabaseFromFileName($file)
    {
        $fileDatabaseName = substr($file, 0, strlen($file) - 11);
        return $fileDatabaseName;
    }

    private function deleteFile($file)
    {
        if($file != $this->backupPath . "/" . $this->lastUpdateTimeFileName)
        {
            if(is_file($file))
            {
                unlink($file);
            }
        }
    }


    public function extractDatabaseNameFromFileName($fileName)
    {

    }

    /**
     * get current date
     * get last backup date
     * @param void $backupTimeout
     */
    public function isNewBackupNeeded($backupTimeout)
    {
        $currentDate = time();
        $lastBackupDate = $this->getLastBackupDate();

        if(($currentDate - $lastBackupDate) > $backupTimeout)
        {
            return true;
        }

        return false;
    }

    private function getLastBackupDate()
    {
        $lastBackupFileContents = file_get_contents($this->backupPath . "/" . $this->lastUpdateTimeFileName);
        return intval($lastBackupFileContents);
    }
   
private function backupTables($host,$user,$pass,$name,       $tables=false, $backup_name=false){ 
    set_time_limit(3000); 
	$mysqli = new mysqli($host,$user,$pass,$name) or die(mysqli_error($mysqli)); 
	$mysqli->select_db($name); 
	$mysqli->query("SET NAMES 'utf8'");
    $queryTables = $mysqli->query('SHOW TABLES'); 
	
	while($row = $queryTables->fetch_row()) 
	{ 
		$target_tables[] = $row[0]; 
	}   
	
	if($tables !== false) 
	{ 
		$target_tables = array_intersect( $target_tables, $tables); 
	}
	
	
    $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
    foreach($target_tables as $table){
		
		
        if (empty($table)){ continue; } 
        $result = $mysqli->query('SELECT * FROM `'.$table.'`');     
		
		$fields_amount=$result->field_count;  
		$rows_num=$mysqli->affected_rows; 
		
		
		$res = $mysqli->query('SHOW CREATE TABLE '.$table); 
		
		//var_dump(is_object($res, "fetch_row"));
		
		echo "Current table name: " . $table . "\n";
				
		
		if(!method_exists($res, "fetch_row"))
		{
			echo "Method dont exists" . "\n";
			var_dump($res);
			
			continue;
		}
			
		$TableMLine=@$res->fetch_row(); 
		
        $content .= "\n\n".$TableMLine[1].";\n\n";   $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
        for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
            while($row = $result->fetch_row())  { //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0 )  {$content .= "\nINSERT INTO ".$table." VALUES";}
                    $content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}     if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";} $st_counter=$st_counter+1;
            }
        } $content .="\n\n\n";
    }
    $content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
    $backup_name = $backup_name ? $backup_name : $name.'___('.date('H-i-s').'_'.date('d-m-Y').').sql';
    ob_get_clean(); //header('Content-Type: application/octet-stream');  header("Content-Transfer-Encoding: Binary");  header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($content, '8bit'): strlen($content)) );    header("Content-disposition: attachment; filename=\"".$backup_name."\""); 
    file_put_contents($backup_name, $content);
}  

}
