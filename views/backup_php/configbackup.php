<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Config backup login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="backup_php-configbackup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'configbackup-form']); ?>

                <?= $form->field($model, 'my_host')->textInput(['autofocus' => true]) ?>
                <?= $form->field($model, 'my_user_name')->textInput() ?>
                <?= $form->field($model, 'my_password')->passwordInput() ?>
                <?= $form->field($model, 'db_backup')->textInput() ?>

                <p>To copy database for creating backup</p>        
                <?= $form->field($model, 'useCopyDB')->checkbox() ?>
                <p>To compress output file</p>
                <?= $form->field($model, 'compress')->checkbox() ?>

                <div class="form-group">
                    <?= Html::submitButton('Next', ['class' => 'btn btn-primary', 'name' => 'next-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
