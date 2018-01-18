<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ConfigBackupForm extends Model
{
    public $my_host;
    public $my_user_name;
    public $my_password;
    public $db_backup;
    public $db_backup_path;
    public $to_exclude_data;
    public $useCopyDB;
    public $tmp_prefix;
    public $compress;
    public $row_block;
    public $row_block_limit;
                    
    public function __construct($params = [], $config = [])
    {
        $this->my_host = $params['host'];
        $this->my_user_name = $params['uname'];
        $this->my_password = $params['upassword'];
        $this->db_backup = $params['user_db'];
        $this->db_backup_path = $params['db_backup_path'];
        $this->useCopyDB = $params['useCopyDB'];
        $this->tmp_prefix = $params['tmp_prefix'];
        $this->compress = $params['compress'];
        $this->row_block = $params['row_block'];
        $this->row_block_limit = $params['row_block_limit'];
        $this->to_exclude_data = $params['to_exclude_data'];
        
        parent::__construct($config);
    }
    
    public function rules()
    {
        return [
            [['my_host', 'my_user_name','my_password', 'db_backup', 'useCopyDB', 'compress'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    public function getConfig()
    {
        return    [ 'host' => $this->my_host,
                    'uname' => $this->my_user_name,
                    'upassword' => $this->my_password,
                    'user_db' => $this->db_backup,
                    'db_backup_path' => $this->db_backup_path,
                    'useCopyDB' => $this->useCopyDB,
                    'tmp_prefix' => $this->tmp_prefix,
                    'compress' => $this->compress,
                    'row_block' => $this->row_block,
                    'row_block_limit' => $this->row_block_limit,
                    'to_exclude_data' => $this->to_exclude_data];
    }
}
