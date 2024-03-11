<?php
use app\modules\orders_list\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $statuses array */
/* @var $search_types array */
/* @var $modes array */
/* @var $services array */
/* @var $selected_status string */
/* @var $search string|null */
/* @var $selected_search_type int|null */
/* @var $selected_service_id int|null */
/* @var $selected_mode string|null */
/* @var $total_orders_number int */
/* @var $services_total_orders_number int */
/* @var $orders array */
/* @var $pagination yii\data\Pagination */

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
                <li class="active"><a href="<?= Url::toRoute(['/orders_list/order/list']) ?>"><?= Module::t('list', 'Orders') ?></a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <ul class="nav nav-tabs p-b">
        <li <?php if (!$selected_status): ?>class="active"<?php endif; ?>>
            <a href="<?= Url::toRoute(['/orders_list/order/list']) ?>">
                <?= Module::t('list', 'All orders') ?>
            </a>
        </li>
        <?php foreach ($statuses as $status): ?>
            <li <?php if ($selected_status === $status['status']): ?>class="active"<?php endif; ?>>
                <a href="<?= Url::toRoute(['/orders_list/order/list', 'status' => $status['status']]) ?>">
                    <?= $status['title'] ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="pull-right custom-search">
            <form class="form-inline" action="<?= Url::current() ?>" method="get">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode($search) ?>" placeholder="<?= Module::t('list', 'Search orders') ?>">
                    <span class="input-group-btn search-select-wrap">

                    <select class="form-control search-select" name="search-type">
                        <?php foreach ($search_types as $code => $type): ?>
                            <option value="<?= $code ?>" <?php if ($code == $selected_search_type): ?>selected=""<?php endif; ?>><?= $type['title'] ?></option>
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
            <th><?= Module::t('list', 'ID') ?></th>
            <th><?= Module::t('list', 'User') ?></th>
            <th><?= Module::t('list', 'Link') ?></th>
            <th><?= Module::t('list', 'Quantity') ?></th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?= Module::t('list', 'Service') ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li <?php if (!$selected_service_id): ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::current(['service_id' => null]) ?>">
                                <?= Module::t('list', 'All') ?> (<?= $services_total_orders_number ?>)
                            </a>
                        </li>
                        <?php foreach ($services as $service): ?>
                            <li
                                <?php if ($selected_service_id == $service['id']): ?>
                                    class="active"
                                <?php elseif (!$service['orders_number']): ?>
                                    class="disabled" aria-disabled="true"
                                <?php endif; ?>>
                                <a href="<?= Url::current(['service_id' => $service['id']]) ?>">
                                    <span class="label-id"><?= $service['orders_number'] ?></span> <?= $service['name'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th><?= Module::t('list', 'Status') ?></th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?= Module::t('list', 'Mode') ?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li <?php if (!$selected_mode): ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::current(['mode' => null]) ?>">
                                <?= Module::t('list', 'All') ?>
                            </a>
                        </li>
                        <?php foreach ($modes as $mode): ?>
                            <li <?php if ($selected_mode === $mode['mode']): ?>class="active"<?php endif; ?>>
                                <a href="<?= Url::current(['mode' => $mode['mode']]) ?>">
                                    <?= $mode['title'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th><?= Module::t('list', 'Created') ?></th>
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
                    <span class="label-id"><?= (int) $services[$order['service_id']]['orders_number'] ?></span> <?= Html::encode($services[$order['service_id']]['name']) ?>
                </td>
                <td><?= $statuses[$order['status']]['title'] ?></td>
                <td><?= $modes[$order['mode']]['title'] ?></td>
                <td><span class="nowrap"><?= date('Y-m-d', $order['created_at']) ?></span><span class="nowrap"><?=  date('H:i:s', $order['created_at']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="row">
        <div class="col-sm-8">
            <nav>
                <?= LinkPager::widget(['pagination' => $pagination]) ?>
            </nav>
        </div>
        <div class="col-sm-4 pagination-counters">
            <?= $pagination->getOffset() + 1 ?> <?= Module::t('list', 'to') ?> <?= $pagination->getOffset() + count($orders) ?> <?= Module::t('list', 'of') ?> <?= $total_orders_number ?>
        </div>
    </div>
</div>
