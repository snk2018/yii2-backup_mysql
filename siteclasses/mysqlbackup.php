<?php

namespace app\siteclasses;

class mysqlbackup {
    
    private $mysqli;
    private $params = array();
    private $mtables = array();
    private $backup_file_name = '';
    
    function __construct($params){
        $this->params = $params;
        $this->backup_file_name = "sql-backup-".$this->params['user_db']."-".date( "d-m-Y_h-i-s").".sql";
        if (!is_dir ( $this->params['db_backup_path'] )) {
                mkdir ( $this->params['db_backup_path'], 0700, true );
         }
    }

    function ContentsHead(){
        $contents = "-- Database: `".$this->params['user_db']."` --\n\n";
        $contents .= "DROP SCHEMA IF EXISTS ".$this->params['user_db'].";\nCREATE SCHEMA ".$this->params['user_db'].";\nUSE ".$this->params['user_db'].";\n";

        return $contents;
    }

    private function ConnectDB(){
        $this->mysqli = new \mysqli($this->params['host'], $this->params['uname'], $this->params['upassword'], $this->params['user_db']);
        if ($this->mysqli->connect_error) {
            //die('Error : ('. $this->mysqli->connect_errno .') '. $this->mysqli->connect_error);
            return false;
        }
        else{
            return true;
        } 
    }
    
    
    function CreateList(){
        if (!$this->ConnectDB()) {
            $this->mtables = array();;
        }
        else{
            $results = $this->mysqli->query("SHOW TABLES");
            while($row = $results->fetch_array()){
                $results_ = $this->mysqli->query("SHOW CREATE TABLE ".$row[0]);
                $fields = $results_->fetch_fields();
                if($fields[0]->name!=='Table') continue;
                $results_ = $this->mysqli->query("SHOW COLUMNS FROM ".$row[0]);
                if(array_key_exists($row[0],$this->params['to_exclude_data'])){
                    while($row_ = $results_->fetch_array())
                            if (!in_array($row_[0], $this->params['to_exclude_data'][$row[0]]))
                                $this->mtables[$row[0]][] = ['Field'=>"`".$row_[0]."`",'Null'=>$row_[2],'Default'=>$row_[4],];
                }    
                else {
                    while($row_ = $results_->fetch_array())
                        $this->mtables[$row[0]][] = ['Field'=>"`".$row_[0]."`",'Null'=>$row_[2],'Default'=>$row_[4],];
                }
            }
        } 
        return $this->mtables;
    }

    function DropTables($tables){
        if(!$this->params['useCopyDB']) return FALSE;
        if (!$this->ConnectDB()) return FALSE;
        
        foreach ($tables as $table) $this->mysqli->query("DROP TABLE IF EXISTS `".$table."`");
        
        return TRUE;
    }
    
    function CopyTables($tables){
        if(!$this->params['useCopyDB']) return $tables;
        $prefix = $this->params['tmp_prefix'];
        $copytables = array(); 
        if (!$this->ConnectDB()) return $copytables;
        
        $query ='';
        foreach ($tables as $table){
            $this->mysqli->query("LOCK TABLES `".$table."` READ,`".$prefix.$table."` WRITE;");
            $query .= "CREATE TABLE `".$prefix.$table."` AS SELECT * FROM `".$table."`;";
            $copytables[] = $prefix.$table;
        }
        
        if(!$this->mysqli->multi_query($query."UNLOCK TABLES")){
            return array();  
        }

        return $copytables;  
    }    
    
    function BackupTabletoFile($prefixtable,$n){
        if (!$this->ConnectDB()) return -1;
        $prefix = $this->params['tmp_prefix'];
        $limit = $this->params['row_block']*$this->params['row_block_limit'];
        $rl = $this->params['row_block'];
        
        $this->params['useCopyDB'] ? $table = substr($prefixtable,strlen($prefix)) : $table = $prefixtable;
        $fp = fopen($this->params['db_backup_path'].$this->backup_file_name ,'a+');
        $datasql = "-- Table `".$table."` --\n";
        $result=0;
        if($n==0)$datasql .= "DROP TABLE IF EXISTS `".$table."`;\n";
        $results = $this->mysqli->query("SHOW CREATE TABLE ".$table);
//        if($results === false) return 0;
        while($row = $results->fetch_array()){
            $datasql .= $row[1].";\n\n";
        }
        
        $this->mysqli->query("LOCK TABLES `".$prefixtable."` WRITE");
        $datasql .= "LOCK TABLES `".$table."` WRITE;\n";
        $columns='';
        foreach ($this->mtables[$table] as $column) $columns .=$column['Field'].",";
        $results = $this->mysqli->query("SELECT ".rtrim($columns,',')." FROM ".$prefixtable." LIMIT ".$n.",".$limit);
        $row_count = $results->num_rows;
        $fields = $results->fetch_fields();
        $fields_count = count($fields);

        $insert_head = "INSERT INTO `".$table."` (";
        for($i=0; $i < $fields_count; $i++){
            $insert_head  .= "`".$fields[$i]->name."`";
                if($i < $fields_count-1){
                        $insert_head  .= ', ';
                    }
        }
        $insert_head .=  ")";
        $insert_head .= " VALUES\n";
        
        $start = microtime(true);
        
        while($row_count>0){
            $start_chunk = microtime(true);
            $r = 0;
            while($row = $results->fetch_array()){
                if(($r % $rl)  == 0){
                    $datasql .= $insert_head;
                }
                $datasql .= "(";
                for($i=0; $i < $fields_count; $i++){
                    $row_content = '';
                    if(empty($row[$i]) && (strcasecmp($this->mtables[$table][$i]['Null'],'YES')==0)){
                        $row_content = 'NULL';//empty($this->mtables[$table][$i]['Default']) ? 'NULL': $this->mtables[$table][$i]['Default'];
                        $t__[]=$row_content;
                        $datasql .=  $row_content;
                    }
                    else{
                        $row_content =  str_replace("\n","\\n",$this->mysqli->real_escape_string($row[$i]));
                   
                        switch($fields[$i]->type){
                            case 8: case 3:
                                $datasql .=  $row_content;
                                break;
                            default:
                                $datasql .= "'". $row_content ."'";
                        }
                    }
                    if($i < $fields_count-1) $datasql  .= ', ';
                }
                ($r+1) == $row_count || ($r % $rl) == $rl-1 ? $datasql .= ");\n" : $datasql .= "),\n";
                $r++;
                $result = fwrite($fp, $datasql);
                $datasql = '';
            }
            $end_chunk=microtime(true);
            if(($start+ini_get('max_execution_time')-$end_chunk)/($end_chunk-$start_chunk)<1.5){$result=$n; break;} 
            $result=0;
            $results = $this->mysqli->query("SELECT * FROM ".$prefixtable." LIMIT ".++$n*$limit.",".$limit);
            $row_count = $results->num_rows;
        }
        $this->mysqli->query("UNLOCK TABLES");
        $datasql .= "UNLOCK TABLES;\n\n";
        fwrite($fp, $datasql);
        fclose($fp); 
        return $result;
    }

    function SaveBackup($datasql){

        if (!is_dir ( $this->params['db_backup_path'] )) {
                mkdir ( $this->params['db_backup_path'], 0777, true );
         }

        $backup_file_name = "sql-backup-".date( "d-m-Y--h-i-s").".sql";

        $fp = fopen($this->params['db_backup_path'].$backup_file_name ,'w+');
        $info = '';
        if (($result = fwrite($fp, $datasql))) {
            $info =  "Backup file created '--$backup_file_name' ($result)";
        }
        fclose($fp); 
        
        return ['info'=>$info,'db_backup_path'=>$this->params['db_backup_path'].$backup_file_name];
    }
    
    function GetInfoBackup(){
        $info =  "Backup file created '--$this->backup_file_name'";
        return ['info'=>$info,'db_backup_path'=>$this->params['db_backup_path'].$this->backup_file_name];
    }

    function SaveBackupZip(){
        if(!$this->params['compress']) return ;
        if(is_file($this->params['db_backup_path'].rtrim($this->backup_file_name,".sqltargz").".tar.gz")) return;
        $backup_file_name = $this->params['db_backup_path'].$this->backup_file_name.".tar";
        try {
            $tarArchive = new \PharData($backup_file_name);
            $tarArchive->addFile($this->params['db_backup_path'].$this->backup_file_name,$this->backup_file_name);
            $tarArchive->compress(\Phar::GZ);
        } catch (Exception $e) {
            return ;          
        }
        unlink($this->params['db_backup_path'].$this->backup_file_name);
        unlink($this->params['db_backup_path'].$this->backup_file_name.".tar");
        $this->backup_file_name = rtrim($this->backup_file_name,".sql").".tar.gz";       
    }
     
    function __destruct(){
    }
}
