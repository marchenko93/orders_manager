<?php

namespace app\modules\listing\controllers;

use app\modules\listing\models\ListingOrder;
use Yii;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OrderController extends Controller
{
    protected const ORDERS_PER_PAGE = 100;

    public function actionList(string $status = ''): string
    {
        $listingOrder = new ListingOrder();
        $statusCode = null;
        if ($status) {
            $statusCode = $listingOrder->getStatusCodeByName($status);
            if (is_null($statusCode)) {
                throw new BadRequestHttpException('Invalid status.');
            }
        }

        $request = Yii::$app->request;
        $modeCode = null;
        $mode = $request->get('mode');
        if (!is_null($mode)) {
            $modeCode = $listingOrder->getModeCodeByName($mode);
            if (is_null($modeCode)) {
                throw new BadRequestHttpException('Invalid mode.');
            }
        }

        $search = $request->get('search');
        $searchTypeCode = $request->get('search-type');
        if ($searchTypeCode) {
            $searchTypes = $listingOrder->getSearchTypes();
            if (!array_key_exists($searchTypeCode, $searchTypes)) {
                throw new BadRequestHttpException('Invalid search type.');
            }
        }

        $serviceId = $request->get('service_id');
        $services = $listingOrder->getServices($statusCode, $modeCode, $searchTypeCode, $search);
        if (!is_null($serviceId)) {
            if (!array_key_exists($serviceId, $services)) {
                throw new BadRequestHttpException('Invalid service.');
            }
        }

        $query = $listingOrder->getOrdersQuery($statusCode, $modeCode, $serviceId, $searchTypeCode, $search);
        $totalOrdersNumber = $query->count();
        $pagination = new Pagination([
            'pageSizeLimit' => [1, self::ORDERS_PER_PAGE],
            'defaultPageSize' => self::ORDERS_PER_PAGE,
            'totalCount' => $totalOrdersNumber,
        ]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        return $this->render('list', [
            'orders' => $query->all(),
            'statuses' => $listingOrder->getStatuses(),
            'selected_status' => $status,
            'modes' => $listingOrder->getModes(),
            'selected_mode' => $mode,
            'services' => $services,
            'selected_service_id' => $serviceId,
            'search_types' => $listingOrder->getSearchTypes(),
            'selected_search_type' => $searchTypeCode,
            'search' => $search,
            'pagination' => $pagination,
            'orders_per_page' => static::ORDERS_PER_PAGE,
            'total_orders_number' => $totalOrdersNumber,
        ]);
    }
}
