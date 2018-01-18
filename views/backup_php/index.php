<?php

$this->title = 'My Yii Backup MySQl DataBase';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>WARNING!</h1>

        <p class="lead">Backuping a database by the PHP.</p>

        <p><a class="btn btn-lg btn-success" href = "<?php echo Yii::$app->urlManager->createUrl(['backup_php/createbackup']) ?>" > Get started DB backup &raquo;</a></p>

    </div>

</div>
