<?php

namespace app\models;

use Yii;
use yii\base\Model;

class DownloadForm extends Model
{
      public function getAll()
      {
             $data = self::find()->all();
             return $data;
      }

}