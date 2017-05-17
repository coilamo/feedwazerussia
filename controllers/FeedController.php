<?php

namespace app\controllers;

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
                'only' => ['index', 'view', 'create', 'delete', 'getstreet'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'delete', 'getstreet'],
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
        $searchModel = new FeedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
                    'hide' => false
                ]);
            }

            $lat = $polyline[0];
            $lon = $polyline[1];
            
            $obj = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/timezone/json?location=" . $lat . "," . $lon . "&timestamp=" . time() . "&key=AIzaSyAtNzSdKyxpLeQPQaVn5vdNK6qvo0SKJLg", true));
            
            
            if (empty($model->starttime))
            {
                $model->starttime = Yii::$app->formatter->asDate('now', 'yyyy-MM-DD') . 'T'
                        .Yii::$app->formatter->asTime('now', 'HH:mm:ss') . '+00:00';
            }
            else
            {
                $model->starttime =  $model->starttime . "+" . gmdate("H:i", $obj->rawOffset);
            }
            $model->endtime  =  $model->endtime . "+" . gmdate("H:i", $obj->rawOffset);

            if ($model->save())
            {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else
            {
                return $this->render('create', [
                    'model' => $model,
                    'hide' => false
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'hide' => true
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
}
