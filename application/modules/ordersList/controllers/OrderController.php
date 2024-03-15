<?php

namespace ordersList\controllers;

use ordersList\models\OrdersList;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * OrderController
 */
class OrderController extends Controller
{
    private const string DEFAULT_LANGUAGE = 'en-US';

    /**
     * @param string $status
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionList(string $status = ''): string
    {
        $ordersList = new OrdersList();
        if (!$ordersList->load(Yii::$app->request->get())) {
            throw new BadRequestHttpException('An error occurred while loading input parameters.');
        }

        $orders = $ordersList->getOrders();

        return $this->render('list', [
            'columnsToDisplay' => $ordersList->getColumnsToDisplay(),
            'filters' => $ordersList->filters,
            'search' => $ordersList->search,
            'orders' => $orders,
            'pagination' => $ordersList->pagination,
            'queryParams' => Yii::$app->request->getQueryParams(),
            'language' => (Yii::$app->language !== self::DEFAULT_LANGUAGE) ? Yii::$app->language : null,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionExport(): Response
    {
        $ordersList = new OrdersList();
        if (!$ordersList->load(Yii::$app->request->get())) {
            throw new BadRequestHttpException('An error occurred while loading input parameters.');
        }

        return $ordersList->exportOrders();
    }
}
