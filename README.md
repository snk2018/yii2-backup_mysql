<p align="center">
    <h1 align="center">Backuping a MySQL database by the PHP.</h1>
    <br>
</p>

Backuping a MySQL database by the PHP, based on Yii 2 Basic Project Template (http://www.yiiframework.com/).


REQUIREMENTS
------------

The minimum requirement by this project that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project using the following command:

~~~
git clone https://github.com/snk2018/yii2-backup_mysql basic
.../basic/php composer.phar create-project
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

~~~
http://localhost/basic/web/
~~~
Menu -> `Home backuping databease` or
~~~
http://localhost/basic/web/index.php?r=backup_php/index
~~~

### or add

~~~
"require": {
    "snk2018/yii2-backup_mysql": "*"
    },
    "repositories":[
        {
            "type":"package",
            "package":{
                "name":"snk2018/yii2-backup_mysql",
                "version":"1.0.0",
                "source":{
                     "type":"git",
                     "url":"https://github.com/snk2018/yii2-backup_mysql",
                     "reference":"master"
                }
            }
        }
    ]   
~~~

to the require section of your `composer.json` file.

CONFIGURATION
-------------

### Database

Edit the file `siteclasses/config.php` with real data, for example:

```php
return [
    'host'=> 'localhost',  //a host name or an IP address
    'uname' => 'name',  //the MySQL user name.
    'upassword' => 'password', //password of user
    'user_db' => 'world_x', //database name
    'db_backup_path' => __DIR__ .'/../backupfiles/', //the absolute path to the backup files with access rights -rwx-
    'useCopyDB' => FALSE, //to copy database for creating backup
    'tmp_prefix' => 'copy', //prefix for replica tables
    'compress' => FALSE, //to compress output file
    'row_block' => 512, //limit the number of rows in the INSERT
    'row_block_limit' => 3, //row_block*row_block_limit the number of rows returned by the SELECT
    'to_exclude_data' => ['countryinfo' => ['_id',],] //[name table]=>[] - the table to exclude,[name table]=>[name row,] - the rows of table to exclude
];
```

**NOTES:**
Example Databases `world_x` from `https://dev.mysql.com/doc/index-other.html`
Refer to the `README` in `world_x-db.zip` to set up the world_x database

