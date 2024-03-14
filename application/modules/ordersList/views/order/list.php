<?php
use ordersList\Module;
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
/* @var $orders_number_for_selected_service int */
/* @var $orders_number_for_all_services int */
/* @var $orders array */
/* @var $pagination yii\data\Pagination */
/* @var $orders_per_page int */
/* @var $columns array */
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
        <li <?php if (!$selected_status): ?>class="active"<?php endif; ?>>
            <a href="<?= Url::toRoute(['/orders-list/order/list', 'lang' => $language]) ?>">
                <?= Module::t('list', 'All orders') ?>
            </a>
        </li>
        <?php foreach ($statuses as $status): ?>
            <li <?php if ($selected_status === $status['status']): ?>class="active"<?php endif; ?>>
                <a href="<?= Url::toRoute(['/orders-list/order/list', 'status' => $status['status'], 'lang' => $language]) ?>">
                    <?= $status['label'] ?>
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

                    <select class="form-control search-select" name="search-type">
                        <?php foreach ($search_types as $code => $type): ?>
                            <option value="<?= $code ?>" <?php if ($code == $selected_search_type): ?>selected=""<?php endif; ?>><?= $type['label'] ?></option>
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
            <?php foreach ($columns as $column): ?>
                <?php if ('service_orders_number' === $column['attribute']): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php if ('service_name' === $column['attribute']): ?>
                    <th class="dropdown-th">
                        <div class="dropdown">
                            <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?= $column['label'] ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                <li <?php if (!$selected_service_id): ?>class="active"<?php endif; ?>>
                                    <a href="<?= Url::current(['service_id' => null]) ?>">
                                        <?= Module::t('list', 'All') ?> (<?= $orders_number_for_all_services ?>)
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
                <?php elseif ('mode' === $column['attribute']): ?>
                    <th class="dropdown-th">
                        <div class="dropdown">
                            <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?= $column['label'] ?>
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
                                            <?= $mode['label'] ?>
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
                    <span class="label-id"><?= (int) $services[$order['service_id']]['orders_number'] ?></span> <?= Html::encode($services[$order['service_id']]['name']) ?>
                </td>
                <td><?=$order['status'] ?></td>
                <td><?= Html::encode($order['mode']) ?></td>
                <?php $createdAt = explode(' ', $order['created_at']); ?>
                <td><span class="nowrap"><?= $createdAt[0] ?></span><span class="nowrap"><?=  $createdAt[1] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($orders_number_for_selected_service > $orders_per_page): ?>
        <div class="row">
            <div class="col-sm-8">
                <nav>
                    <?= LinkPager::widget(['pagination' => $pagination]) ?>
                </nav>
            </div>
            <div class="col-sm-4 pagination-counters">
                <?= $pagination->getOffset() + 1 ?> <?= Module::t('list', 'to') ?> <?= $pagination->getOffset() + count($orders) ?> <?= Module::t('list', 'of') ?> <?= $orders_number_for_selected_service ?>
            </div>
        </div>
    <?php endif; ?>
    <br>
    <a href="<?= Url::current(['export' => 1]) ?>"><?= Module::t('list', 'Save result') ?></a>
</div>
