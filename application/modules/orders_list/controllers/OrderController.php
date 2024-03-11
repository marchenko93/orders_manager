<?php

namespace app\modules\orders_list\controllers;

use app\modules\orders_list\models\OrdersList;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OrderController extends Controller
{
    protected const ORDERS_PER_PAGE = 100;

    public function actionList(string $status = ''): string
    {
        $ordersList = new OrdersList();
        $statusCode = null;
        if ($status) {
            $statusCode = $ordersList->getStatusCodeByName($status);
            if (is_null($statusCode)) {
                throw new BadRequestHttpException('Invalid status.');
            }
        }

        $request = Yii::$app->request;
        $modeCode = null;
        $mode = $request->get('mode');
        if (!is_null($mode)) {
            $modeCode = $ordersList->getModeCodeByName($mode);
            if (is_null($modeCode)) {
                throw new BadRequestHttpException('Invalid mode.');
            }
        }

        $search = $request->get('search');
        $searchTypeCode = $request->get('search-type');
        if ($searchTypeCode) {
            $searchTypes = $ordersList->getSearchTypes();
            if (!$ordersList->isSearchTypeValid($searchTypeCode)) {
                throw new BadRequestHttpException('Invalid search type.');
            }
        }

        $serviceId = $request->get('service_id');
        $services = $ordersList->getServices($statusCode, $modeCode, $searchTypeCode, $search);
        if (!is_null($serviceId)) {
            if (!$ordersList->isServiceValid($serviceId, $services)) {
                throw new BadRequestHttpException('Invalid service.');
            }
        }

        $ordersQuery = $ordersList->getOrdersQuery($statusCode, $modeCode, $serviceId, $searchTypeCode, $search);
        $totalOrdersNumber = $ordersQuery->count();
        $pagination = new Pagination([
            'pageSizeLimit' => [1, self::ORDERS_PER_PAGE],
            'defaultPageSize' => self::ORDERS_PER_PAGE,
            'totalCount' => $totalOrdersNumber,
        ]);
        $ordersQuery->offset($pagination->offset)->limit($pagination->limit);

        return $this->render('list', [
            'orders' => $ordersQuery->all(),
            'statuses' => $ordersList->getStatuses(),
            'selected_status' => $status,
            'modes' => $ordersList->getModes(),
            'selected_mode' => $mode,
            'services' => $services,
            'selected_service_id' => $serviceId,
            'search_types' => $ordersList->getSearchTypes(),
            'selected_search_type' => $searchTypeCode,
            'search' => $search,
            'pagination' => $pagination,
            'orders_per_page' => static::ORDERS_PER_PAGE,
            'total_orders_number' => $totalOrdersNumber,
            'services_total_orders_number' => $ordersList->getServicesTotalOrdersNumber($services),
        ]);
    }
}
