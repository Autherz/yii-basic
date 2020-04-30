<?php 

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use app\models\Todo;

class TodoController extends ActiveController
{
    public $modelClass = 'app\models\Todo' ;

    // public function behaviors()
    // {

    // }

    public function actionGet()
    {  
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Todo();
        return $model->find();
    }

    public function actionPost()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Todo();
        $model->title = Yii::$app->request->post('title');

        $collection = Yii::$app->mongodb->getCollection('todo');
        $id = $collection->insert($model);

        return $id;
    }
}

