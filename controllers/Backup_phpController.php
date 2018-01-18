<?php

namespace app\controllers;

use common\models\LoginForm;
use app\models\ContactForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\SignupForm;
use app\models\DownloadForm;
use app\models\ConfigBackupForm;
use app\siteclasses\mysqlbackup;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class Backup_phpController extends Controller
{
    public $layout = 'backup_php';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $session = Yii::$app->session;

        if (!isset($session['event']) || Yii::$app->controller->action->id !== 'createbackup'){
            $session['event']=0;
            $session['mtables'] = array();
            $session['contents'] = '';
        }
            
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @inheritdoc
     */
    private function ConnectParams()
    {
        return require(__DIR__ . '/../siteclasses/config.php');
    }

    
    public function actionCreatebackup()
    {
        $session = Yii::$app->session;
        $event = $session['event'];       
        $content='';
        switch($event){
                        case 0:
                            $event_ ='BACKUP';
                            $session['offsetbackup']=0;
                            $model = new ConfigBackupForm($this->ConnectParams());
                            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                                $session['mysqlbackup'] = new mysqlbackup($model->getConfig());
                                $session['contents'] = $session['mysqlbackup']->ContentsHead();
                                $mtables = $session['mysqlbackup']->CreateList();
                                if(empty($mtables))
                                    $content='NOT TABLES';
                                else{    
                                    $mtables_ = array();
                                    foreach($mtables as $table => $column){
                                    $content .= "<tr><td>".$table."</td><td> INCLUDED</td></tr>";
                                    $mtables_[]=$table;
                                    }
                                    $mtables_ = $session['mtables']=$session['mysqlbackup']->CopyTables($mtables_);
                                    $session['currentmtables'] = current($mtables_);
                                    $session['event'] = $session['event']+1;
                                    return $this->render('infobackup',['_event' => $event_,'mtables' => $session['mtables'],'currentmtables' => $session['currentmtables'],'messages' => $content,'offsetbackup' => $session['offsetbackup']]);
                              }
                            }

                            return $this->render('configbackup', [
                                    'model' => $model,
                                    ]);
                            break;
                        case 1:
                            $event_ ='SAVE';
                            $mtables = $session['mtables'];
                            foreach($mtables as $table){
                                next($mtables);

                                if($table===$session['currentmtables']){
                                    $session['offsetbackup'] = $session['mysqlbackup']->BackupTabletoFile($table,$session['offsetbackup']);
                                    $session['contents'] .= $session['offsetbackup'];
                                    if($session['offsetbackup']>0)break;
                                    if($session['offsetbackup']==-1)return $this->render('index',[]);
                                    if ( null === ($key = key($mtables)) )
                                        $session['event'] = $session['event']+1;
                                    $session['currentmtables'] = current($mtables);
                                    break;
                                }
                            }
                            return $this->render('infobackup',['_event' => $event_,'mtables' => $session['mtables'],'currentmtables' => $session['currentmtables'],'messages' => $content,'offsetbackup' => $session['offsetbackup']]);
                            break;
                        case 2:
                            $session['mysqlbackup']->DropTables($session['mtables']);
                            $session['mysqlbackup']->SaveBackupZip();
                        case 3:
                            $event_ ='SAVED';
                            $session['event'] = $session['event']+1;
                            $model = new DownloadForm();
                            if (Yii::$app->request->isPost) {
                                    switch (Yii::$app->request->post('download-button')) {
                                        case 'download':
                                            $session['contents']=$session['mysqlbackup']->GetInfoBackup();
                                            Yii::$app->response->sendFile($session['contents']['db_backup_path'],$session['contents']['info']);
                                        break;    
                                    }
                                            $session['event'] = $session['event']+2;
                            }
                            else $session['contents']=$session['mysqlbackup']->GetInfoBackup();
                            return $this->render('downloadbackup',['messages' => $session['contents']['info']]);
                            break;
                        default:
                            $event_ ='CONFIG';
                            $session['contents'] = '';
                            $session['event'] = 0;
                        return $this->render('index',[]);
                    }        
    }
}
