<?php

namespace app\controllers;

use DateInterval;
use DateTime;
use Yii;
use app\models\Feed;
use app\models\FeedSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * FeedController implements the CRUD actions for Feed model.
 */
class FeedController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'delete', 'getstreet', 'extend'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'delete', 'getstreet', 'extend'],
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    public function beforeAction($action) {
        if(\Yii::$app->user->isGuest)
        {
            $this->redirect(array('site/login'));
        }
        return true;
    }

    /**
     * Lists all Feed models.
     * @return mixed
     */
    public function actionIndex()
    {
        $bulkResult = $this->processBulk();
        $searchModel = new FeedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isBulk' => $bulkResult['isBulk'],
            'bulkAction' => $bulkResult['bulkAction'],
            'bulkResult' => $bulkResult['bulkResult'],
        ]);
    }

    /**
     * Displays a single Feed model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'deleteNotAllowed' => false,
        ]);
    }

    /**
     * Creates a new Feed model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Feed();

        if ($model->load(Yii::$app->request->post())) {    
            // prepare new feed record
            
            $model->author_id = \Yii::$app->user->getId();
            $model->incident_id = "WFR-" . \Yii::$app->security->generateRandomString(16) . '-' . time();
            
            // TODO!
            $model->active = 1;
            $model->mail_send = 1;
            $model->name = "Russian community";
            
            $polyline = explode(' ', $model->polyline);
            if (count($polyline) < 2 || count($polyline) % 2 != 0)
            {
                // Invalid polyline!
                return $this->render('create', [
                    'model' => $model,
                    'hide' => false,
                    'allowedTypes' => $this->getAllowedTypes(),
                ]);
            }

            $lat = $polyline[0];
            $lon = $polyline[1];
            
            $obj = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=" . $lat . "," . $lon . "&timestamp=" . time() . "&key=AIzaSyAtNzSdKyxpLeQPQaVn5vdNK6qvo0SKJLg", true));
            
            
            if (empty($model->starttime))
            {
                $model->starttime = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd') . 'T'
                        .Yii::$app->formatter->asTime('now', 'HH:mm:ss') . '+00:00';
            }
            else
            {
                $model->starttime =  $model->starttime . "+" . gmdate("H:i", $obj->rawOffset);
            }
            $model->endtime  =  $model->endtime . "+" . gmdate("H:i", $obj->rawOffset);
            
            if (empty($model->street))
            {
                $model->street = 'No street';
            }

            if ($model->save())
            {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else
            {
                return $this->render('create', [
                    'model' => $model,
                    'hide' => false,
                    'allowedTypes' => $this->getAllowedTypes(),
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'hide' => true,
                'allowedTypes' => $this->getAllowedTypes(),
            ]);
        }
    }

    /**
     * Updates an existing Feed model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }*/

    /**
     * Deletes an existing Feed model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if ($this->deleteFeedById($id)) {
            return $this->redirect(['index']);
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
            'deleteNotAllowed' => true,
        ]);
    }

    /**
     * Finds the Feed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Feed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Feed::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionGetstreet($lat, $lon)
    {
        $url="https://feed.world.waze.com/FeedManager/getStreet?token=WAZE_COMMUNITY_7c34df4e45&lat=" . $lat . "&lon=" . $lon . "&radius=50";
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        if (!empty($data['result']))
        {
            $name=$data['result'][0]['names'][0];
            echo trim($name);
        }
    }

    public function actionExtend($id)
    {
        $request = Yii::$app->request;
        $days = $request->post('days');

        $model = $this->findModel($id);
        if ($days > 0) {
            $enddate = new DateTime($model->endtime);
            $enddate->add(new DateInterval('P'.intval($days).'D'));
            $model->endtime = $enddate->format('c');
            var_dump($model->endtime);
            $model->update();
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionClone($id)
    {
        $model = $this->findModel($id);
        $model->id = null;
        $model->incident_id = null;
        $model->isNewRecord = true;

        $endtime = new DateTime($model->endtime);
        $model->endtime = $endtime->format('Y-m-d\TH:i:s');

        $starttime = new DateTime($model->starttime);
        $model->starttime = $starttime->format('Y-m-d\TH:i:s');

        return $this->render('create', [
            'model' => $model,
            'hide' => false,
            'allowedTypes' => $this->getAllowedTypes(),
        ]);
    }

    private function processBulk(){
        $action=Yii::$app->request->post('action');

        if(empty($action)) {
            return [
                'isBulk' => false,
                'bulkAction' => $action,
                'bulkResult' => false,
            ];
        }
        $success = true;
        $selection=(array)Yii::$app->request->post('selection');//typecasting

        foreach($selection as $id){
            if ($action == 'r') {
                $success &= $this->deleteFeedById($id, true);
            }
        }

        return [
            'isBulk' => true,
            'bulkAction' => $action,
            'bulkResult' => $success,
        ];
    }

    private function getAllowedTypes() {
        $allowedTypes = array();

        /*if(\Yii::$app->user->identity->country != 2) {*/
            $allowedTypes['CHIT_CHAT'] = Yii::t('app/feed', 'Chat');
        /* } */
        $allowedTypes['POLICE'] = Yii::t('app/feed', 'Police');
        $allowedTypes['JAM'] = Yii::t('app/feed', 'Traffic');
        $allowedTypes['ACCIDENT'] = Yii::t('app/feed', 'Crash');
        $allowedTypes['CONSTRUCTION'] = Yii::t('app/feed', 'Road works');
        $allowedTypes['HAZARD'] = Yii::t('app/feed', 'Hazard');
        $allowedTypes['ROAD_CLOSED'] = Yii::t('app/feed', 'Closure');

        return $allowedTypes;
    }

    private function deleteFeedById($id, $bulk = false) {
        try {
            $feed = $this->findModel($id);
            if ($feed->author_id == \Yii::$app->user->getId()
                || (($feed->author->country == 2) && (\Yii::$app->user->identity->country == 2))) { // Author should be the same or from Belarus
                $this->findModel($id)->delete();
                return true;
            }
            return false;
        } catch (NotFoundHttpException $ex) {
            if (!$bulk) {
                throw $ex;
            } else {
                return false;
            }
        }

    }
}
