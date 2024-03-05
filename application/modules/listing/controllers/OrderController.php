<?php

namespace app\modules\listing\controllers;

use app\modules\listing\models\Order;
use yii;
use yii\web\Controller;

class OrderController extends Controller
{
    public function actionList(string $statusSlug = '')
    {
        $status = Order::getStatusCodeBySlug($statusSlug);
        $orders = Order::getOrders($status);
        return $this->render('index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'current_status_slug' => $statusSlug,
        ]);
    }
}
