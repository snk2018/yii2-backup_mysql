<?php
  function cp_d($src,$dst) {
                               $dir = opendir($src);
                               @mkdir($dst);
                               while(false !== ( $file = readdir($dir)) ) {
                                     if (( $file != '.' ) && ( $file != '..' )) {
                                        if ( is_dir($src . '/' . $file) ) {
                                           cp_d($src . '/' . $file,$dst . '/' . $file);
                                        }
                                        else {
                                           if (!is_readable($dst . '/' . $file)){
                                              copy($src . '/' . $file,$dst . '/' . $file);
                                           }
                                           else echo 'scipping overwite - '.$dst . '/' . $file.PHP_EOL;
                                        }
                                     }
                               }
                              closedir($dir);
                              }; 
  cp_d('vendor/yiisoft/yii2-app-basic/','./');
?>