<?php

namespace ordersList\controllers;

use ordersList\models\OrdersList;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;

class OrderController extends Controller
{
    private const string DEFAULT_LANGUAGE = 'en-US';

    public function actionList(string $status = ''): string
    {
        $request = Yii::$app->request;
        $mode = $request->get('mode');
        $search = $request->get('search');
        $searchType = $request->get('search-type');
        $serviceId = $request->get('service_id');
        $ordersList = new OrdersList([], $status, $mode, $search, $searchType, $serviceId);
        $ordersQuery = $ordersList->getOrdersQuery();

        if ($request->get('export')) {
            $ordersList->exportQueryResultToCsv($ordersQuery);
        }

        $ordersNumberForSelectedService = $ordersList->getOrdersNumberForSelectedService();
        $pagination = new Pagination([
            'pageSizeLimit' => [1, OrdersList::ORDERS_PER_PAGE],
            'defaultPageSize' => OrdersList::ORDERS_PER_PAGE,
            'totalCount' => $ordersNumberForSelectedService,
        ]);
        $ordersQuery->offset($pagination->offset)->limit($pagination->limit);

        return $this->render('list', [
            'orders' => $ordersQuery->all(),
            'statuses' => $ordersList->getStatuses(),
            'selected_status' => $status,
            'modes' => $ordersList->getModes(),
            'selected_mode' => $mode,
            'services' => $ordersList->getServices(),
            'selected_service_id' => $serviceId,
            'search_types' => $ordersList->getSearchTypes(),
            'selected_search_type' => $searchType,
            'search' => $search,
            'pagination' => $pagination,
            'orders_per_page' => OrdersList::ORDERS_PER_PAGE,
            'orders_number_for_selected_service' => $ordersNumberForSelectedService,
            'orders_number_for_all_services' => $ordersList->getOrdersNumberForAllServices(),
            'columns' => $ordersList->getColumns(),
            'language' => Yii::$app->language !== self::DEFAULT_LANGUAGE ? Yii::$app->language : null,
        ]);
    }
}
