<?php

namespace app\modules\listing\controllers;

use app\modules\listing\models\Order;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\HttpException;

class OrderController extends Controller
{
    private const ORDERS_PER_PAGE = 100;

    public function actionList(string $status = '')
    {
        $statusCode = null;
        if ($status) {
            $statusCode = Order::getStatusCodeByName($status);
            if (is_null($statusCode)) {
                throw new HttpException(400, 'Invalid status.');
            }
        }

        $modeCode = null;
        $mode = Yii::$app->request->get('mode');
        if (!is_null($mode)) {
            $modeCode = Order::getModeCodeByName($mode);
            if (is_null($modeCode)) {
                throw new HttpException(400, 'Invalid mode.');
            }
        }

        $query = Order::getOrdersQuery($statusCode, $modeCode);
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
            'selected_status' => $status,
            'modes' => Order::MODES,
            'selected_mode' => $mode,
            'pagination' => $pagination,
            'orders_per_page' => self::ORDERS_PER_PAGE,
            'total_orders_number' => $totalOrdersNumber,
        ]);
    }
}
