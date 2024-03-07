<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
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
                <li class="active"><a href="<?= Url::toRoute(['/listing/order/list']) ?>">Orders</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <ul class="nav nav-tabs p-b">
        <li <?php if (!$selected_status): ?>class="active"<?php endif; ?>>
            <a href="<?= Url::toRoute(['/listing/order/list']) ?>">
                All orders
            </a>
        </li>
        <?php foreach ($statuses as $status): ?>
            <li <?php if ($selected_status === $status['name']): ?>class="active"<?php endif; ?>>
                <a href="<?= Url::toRoute(['/listing/order/list', 'status' => $status['name']]) ?>">
                    <?= $status['title'] ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="pull-right custom-search">
            <form class="form-inline" action="<?= Url::current() ?>" method="get">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="<?= Html::encode($search) ?>" placeholder="Search orders">
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
            <th>ID</th>
            <th>User</th>
            <th>Link</th>
            <th>Quantity</th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Service
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li <?php if (!$selected_service_id): ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::current(['service_id' => null]) ?>">
                                All (<?= $total_orders_number ?>)
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
            <th>Status</th>
            <th class="dropdown-th">
                <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Mode
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li <?php if (!$selected_mode): ?>class="active"<?php endif; ?>>
                            <a href="<?= Url::current(['mode' => null]) ?>">
                                All
                            </a>
                        </li>
                        <?php foreach ($modes as $mode): ?>
                            <li <?php if ($selected_mode === $mode['name']): ?>class="active"<?php endif; ?>>
                                <a href="<?= Url::current(['mode' => $mode['name']]) ?>">
                                    <?= $mode['title'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th>Created</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= $order['user']['first_name'] . ' ' . $order['user']['last_name'] ?></td>
                <td class="link"><?= $order['link'] ?></td>
                <td><?= $order['quantity'] ?></td>
                <td class="service">
                    <span class="label-id"><?= $services[$order['service_id']]['orders_number'] ?></span> <?= $order['service']['name'] ?>
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
            <?= $pagination->getOffset() + 1 ?> to <?= $pagination->getOffset() + count($orders) ?> of <?= $total_orders_number ?>
        </div>
    </div>
</div>
