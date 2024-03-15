<?php
use ordersList\Module;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $columnsToDisplay array */
/* @var $filters array */
/* @var $search string|null */
/* @var $orders array */
/* @var $pagination Pagination */
/* @var $queryParams array */
/* @var $language string|null */

$this->title = Module::t('list', 'Orders');
?>

<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?= Url::toRoute(['/orders-list/order/list', 'lang' => $language]) ?>"><?= Module::t('list', 'Orders') ?></a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <ul class="nav nav-tabs p-b">
        <li <?php if (!$filters['status']['selectedValue']): ?>class="active"<?php endif; ?>>
            <a href="<?= Url::toRoute(['/orders-list/order/list', 'lang' => $language]) ?>">
                <?= Module::t('list', 'All orders') ?>
            </a>
        </li>
        <?php foreach ($filters['status']['values'] as $code => $status): ?>
            <li <?php if ($filters['status']['selectedValue'] === $status): ?>class="active"<?php endif; ?>>
                <a href="<?= Url::toRoute(['/orders-list/order/list', 'status' => $status, 'lang' => $language]) ?>">
                    <?= $filters['status']['labels'][$code] ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="pull-right custom-search">
            <form class="form-inline" action="<?= Url::current() ?>" method="get">
                <?php if ($language): ?>
                    <input type="hidden" name="lang" value="<?= $language ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode($search) ?>" placeholder="<?= Module::t('list', 'Search orders') ?>">
                    <span class="input-group-btn search-select-wrap">

                    <select class="form-control search-select" name="searchType">
                        <?php foreach ($filters['searchType']['labels'] as $code => $label): ?>
                            <option value="<?= $code ?>" <?php if ($code == $filters['searchType']['selectedValue']): ?>selected=""<?php endif; ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                    </span>
                </div>
            </form>
        </li>
    </ul>
    <table class="table order-table">
        <thead>
        <tr>
            <?php foreach ($columnsToDisplay as $column): ?>
                <?php if ('service' === $column['attribute']): ?>
                    <th class="dropdown-th">
                        <div class="dropdown">
                            <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?= $column['label'] ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                <li <?php if (!$filters['service']['selectedValue']): ?>class="active"<?php endif; ?>>
                                    <a href="<?= Url::current(['serviceId' => null]) ?>">
                                        <?= Module::t('list', 'All') ?> (<?= $filters['service']['totalOrdersNumber'] ?>)
                                    </a>
                                </li>
                                <?php foreach ($filters['service']['services'] as $service): ?>
                                    <li
                                        <?php if ($filters['service']['selectedValue'] == $service['id']): ?>
                                            class="active"
                                        <?php elseif (!$service['orders_number']): ?>
                                            class="disabled" aria-disabled="true"
                                        <?php endif; ?>>
                                        <a href="<?= Url::current(['serviceId' => $service['id']]) ?>">
                                            <span class="label-id"><?= $service['orders_number'] ?></span> <?= $service['name'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </th>
                <?php elseif ('mode' === $column['attribute']): ?>
                    <th class="dropdown-th">
                        <div class="dropdown">
                            <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?= $column['label'] ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                <li <?php if (!$filters['mode']['selectedValue']): ?>class="active"<?php endif; ?>>
                                    <a href="<?= Url::current(['mode' => null]) ?>">
                                        <?= Module::t('list', 'All') ?>
                                    </a>
                                </li>
                                <?php foreach ($filters['mode']['values'] as $code => $mode): ?>
                                    <li <?php if ($filters['mode']['selectedValue'] === $mode): ?>class="active"<?php endif; ?>>
                                        <a href="<?= Url::current(['mode' => $mode]) ?>">
                                            <?= $filters['mode']['labels'][$code] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </th>
                <?php else: ?>
                    <th><?= $column['label'] ?></th>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int) $order['id'] ?></td>
                <td><?= Html::encode($order['username']) ?></td>
                <td class="link"><?= Html::encode($order['link']) ?></td>
                <td><?= (int) $order['quantity'] ?></td>
                <td class="service">
                    <span class="label-id"><?= (int) $filters['service']['services'][$order['service_id']]['orders_number'] ?>
                    </span> <?= Html::encode($filters['service']['services'][$order['service_id']]['name']) ?>
                </td>
                <td><?=$order['status'] ?></td>
                <td><?= Html::encode($order['mode']) ?></td>
                <?php $createdAt = explode(' ', $order['created_at']); ?>
                <td><span class="nowrap"><?= $createdAt[0] ?></span><span class="nowrap"><?=  $createdAt[1] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($pagination->totalCount > $pagination->pageSize): ?>
        <div class="row">
            <div class="col-sm-8">
                <nav>
                    <?= LinkPager::widget(['pagination' => $pagination]) ?>
                </nav>
            </div>
            <div class="col-sm-4 pagination-counters">
                <?= $pagination->getOffset() + 1 ?>
                <?= Module::t('list', 'to') ?>
                <?= $pagination->getOffset() + count($orders) ?>
                <?= Module::t('list', 'of') ?> <?= $pagination->totalCount ?>
            </div>
        </div>
    <?php endif; ?>
    <br>
    <a href="<?= Url::toRoute(array_merge(['/orders-list/order/export'], $queryParams)) ?>"><?= Module::t('list', 'Save result') ?></a>
</div>
