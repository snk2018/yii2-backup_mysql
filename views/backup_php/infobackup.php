<?php

use yii\helpers\Html;

$this->registerMetaTag(['http-equiv' => 'refresh', 'content' => '0;'.Yii::$app->urlManager->createUrl(['backup_php/createbackup']) ]);

$this->title = 'Progress DB backup';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-createbackup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is the progress backup database page.</p>
    
        <br><?php echo 'Status: '.$_event/*.'->'.$messages*/ ?></br> 
        <br><?php echo 'Info: '.$currentmtables.'  in progress - '.$offsetbackup ?></br> 
    <table>
    <?php  $status = ' SAVED'; if('SAVE'===$_event) foreach ($mtables as $item): ?>
        <tr><?php if($item==$currentmtables) $status = ' WAITING'; echo "<td>".$item."</td><td>".$status."</td>"; ?></tr>
    <?php endforeach?>
        <?php  if('SAVE'!==$_event) echo $messages ?>
    </table>    
</div>
