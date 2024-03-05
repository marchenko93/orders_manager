<?php

namespace app\modules\listing\controllers;

use app\modules\listing\models\Order;
use yii\data\Pagination;
use yii\web\Controller;

class OrderController extends Controller
{
    private const ORDERS_PER_PAGE = 100;

    public function actionList(string $slug = '')
    {
        $status = Order::getStatusCodeBySlug($slug);
        $query = Order::getOrdersQuery($status);
        $totalOrdersNumber = $query->count();
        $pagination = new Pagination([
            'pageSizeLimit' => [1, self::ORDERS_PER_PAGE],
            'defaultPageSize' => self::ORDERS_PER_PAGE,
            'totalCount' => $totalOrdersNumber,
        ]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        return $this->render('list', [
            'orders' => $query->all(),
            'statuses' => Order::STATUSES,
            'current_slug' => $slug,
            'pagination' => $pagination,
            'orders_per_page' => self::ORDERS_PER_PAGE,
            'total_orders_number' => $totalOrdersNumber,
        ]);
    }
}
