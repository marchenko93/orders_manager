<?php

namespace app\modules\listing\controllers;

use app\modules\listing\models\Order;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OrderController extends Controller
{
    private const ORDERS_PER_PAGE = 100;

    public function actionList(string $status = '')
    {
        $statusCode = null;
        if ($status) {
            $statusCode = Order::getStatusCodeByName($status);
            if (is_null($statusCode)) {
                throw new BadRequestHttpException('Invalid status.');
            }
        }

        $request = Yii::$app->request;
        $modeCode = null;
        $mode = $request->get('mode');
        if (!is_null($mode)) {
            $modeCode = Order::getModeCodeByName($mode);
            if (is_null($modeCode)) {
                throw new BadRequestHttpException('Invalid mode.');
            }
        }

        $search = $request->get('search');
        $searchTypeCode = $request->get('search-type');
        if ($searchTypeCode) {
            $searchTypes = Order::getSearchTypes();
            if (!array_key_exists($searchTypeCode, $searchTypes)) {
                throw new BadRequestHttpException('Invalid search type.');
            }
        }

        $serviceId = $request->get('service_id');
        $services = Order::getServices($statusCode, $modeCode, $searchTypeCode, $search);
        if (!is_null($serviceId)) {
            if (!array_key_exists($serviceId, $services)) {
                throw new BadRequestHttpException('Invalid service.');
            }
        }

        $query = Order::getOrdersQuery($statusCode, $modeCode, $serviceId, $searchTypeCode, $search);
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
            'services' => $services,
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
