<?php

return [
    'host'=> 'localhost',
    'uname' => 'name',
    'upassword' => 'password',
    'user_db' => 'world_x',
    'db_backup_path' => __DIR__ .'/../backupfiles/',
    'useCopyDB' => FALSE,
    'tmp_prefix' => 'copy',
    'compress' => FALSE,
    'row_block' => 512,
    'row_block_limit' => 3,
    'to_exclude_data' => ['countryinfo' => ['_id',],] 
];