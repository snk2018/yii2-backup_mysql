<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Download backup file';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is the downloading file page. You may modify the following file to customize its content:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'downloadbackup-form']); ?>
                <h1><?php echo $messages ?></h1>
                
                <div class="form-group">
                    <?= Html::submitButton('Download', ['class' => 'btn btn-primary', 'name' => 'download-button', 'value' => 'download']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
