<?php

namespace app\modules\listing\controllers;

use yii\web\Controller;

class OrderController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}