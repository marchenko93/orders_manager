<?php

namespace app\modules\listing\controllers;

use yii;
use yii\web\Controller;

class OrderController extends Controller
{
    public function actionIndex()
    {
        $db = Yii::$app->db;
        $sql = "
            SELECT o.id, CONCAT(u.first_name, ' ', u.last_name) user, o.link, o.quantity, o.service_id,
                s.name service_name, o.status, o.mode, FROM_UNIXTIME(o.created_at, \"%Y-%m-%d\") created_date,
                FROM_UNIXTIME(o.created_at, \"%h:%i:%s\") created_time
            FROM orders o
                INNER JOIN users u
                    ON o.user_id = u.id
                INNER JOIN services s
                    ON o.service_id = s.id
            ORDER BY id LIMIT 20
        ";
        $orders = Yii::$app->db->createCommand($sql)->queryAll();
        return $this->render('index', [
            'orders' => $orders,
        ]);
    }
}
