<?php

namespace ordersList\controllers;

use ordersList\models\OrdersList;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class OrderController extends Controller
{
    private const string DEFAULT_LANGUAGE = 'en-US';

    public function actionList(string $status = ''): string
    {
        $ordersList = new OrdersList();
        $ordersList->load(Yii::$app->request->get());
        if (!$ordersList->validate()) {
            throw new BadRequestHttpException(implode(' ', $ordersList->getErrorSummary(true)));
        }

        if (Yii::$app->request->get('export')) {
            $ordersList->exportOrders();
        }

        $ordersList->prepareToGetOrders();

        return $this->render('list', [
            'columnsToDisplay' => $ordersList->getColumnsToDisplay(),
            'filters' => $ordersList->filters,
            'totalOrdersNumberWithoutServiceFilter' => $ordersList->getTotalOrdersNumberWithoutServiceFilter(),
            'selectedValues' => $ordersList->attributes,
            'orders' => $ordersList->getOrders(),
            'pagination' => $ordersList->pagination,
            'language' => Yii::$app->language !== self::DEFAULT_LANGUAGE ? Yii::$app->language : null,
        ]);
    }
}
