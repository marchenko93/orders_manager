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

        $search = Yii::$app->request->get('search');
        $mode = Yii::$app->request->get('mode');
        $serviceId = Yii::$app->request->get('service_id');
        $searchTypeCode = Yii::$app->request->get('search-type');
        $modeCode = null;
        if ($search && $searchTypeCode) {
            $searchTypes = Order::getSearchTypes();
            if (!array_key_exists($searchTypeCode, $searchTypes)) {
                throw new HttpException(400, 'Invalid search type.');
            }
            $query = Order::getSearchOrdersQuery($searchTypeCode, $search, $statusCode);
        } else {
            if (!is_null($mode)) {
                $modeCode = Order::getModeCodeByName($mode);
                if (is_null($modeCode)) {
                    throw new HttpException(400, 'Invalid mode.');
                }
            }
            $services = Order::getServices($statusCode, $modeCode);
            if (!is_null($serviceId)) {
                if (!array_key_exists($serviceId, $services)) {
                    throw new HttpException(400, 'Invalid service.');
                }
            }
            $query = Order::getFilterOrdersQuery($statusCode, $modeCode, $serviceId);
        }

        $totalOrdersNumber = $query->count();
        $pagination = new Pagination([
            'pageSizeLimit' => [1, self::ORDERS_PER_PAGE],
            'defaultPageSize' => self::ORDERS_PER_PAGE,
            'totalCount' => $totalOrdersNumber,
        ]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        return $this->render('list', [
            'orders' => $query->all(),
            'statuses' => Order::getStatuses(),
            'selected_status' => $status,
            'modes' => Order::getModes(),
            'selected_mode' => $mode,
            'services' => Order::getServices($statusCode, $modeCode),
            'selected_service_id' => $serviceId,
            'search_types' => Order::getSearchTypes(),
            'selected_search_type' => $searchTypeCode,
            'search' => $search,
            'pagination' => $pagination,
            'orders_per_page' => self::ORDERS_PER_PAGE,
            'total_orders_number' => $totalOrdersNumber,
        ]);
    }
}
