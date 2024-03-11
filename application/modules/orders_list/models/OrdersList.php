<?php

namespace app\modules\orders_list\models;

use app\modules\orders_list\Module;
use yii\db\Query;

class OrdersList
{
    protected const int SEARCH_TYPE_ORDER_ID = 1;
    protected const int SEARCH_TYPE_LINK = 2;
    protected const int SEARCH_TYPE_USERNAME = 3;

    protected array $statuses;
    protected array $modes;
    protected array $searchTypes;

    public function __construct($config = [])
    {
        $this->statuses = [
            Order::STATUS_CODE_PENDING => [
                'status' => 'pending',
                'title' => Module::t('list', 'Pending')
            ],
            Order::STATUS_CODE_IN_PROGRESS => [
                'status' => 'inprogress',
                'title' => Module::t('list', 'In progress')
            ],
            Order::STATUS_CODE_COMPLETED => [
                'status' => 'completed',
                'title' => Module::t('list', 'Completed')
            ],
            Order::STATUS_CODE_CANCELED => [
                'status' => 'canceled',
                'title' => Module::t('list', 'Canceled')
            ],
            Order::STATUS_CODE_ERROR => [
                'status' => 'error',
                'title' => Module::t('list', 'Error')
            ],
        ];
        $this->modes = [
            Order::MODE_CODE_MANUAL => [
                'mode' => 'manual',
                'title' => Module::t('list', 'Manual')
            ],
            Order::MODE_CODE_AUTO => [
                'mode' => 'auto',
                'title' => Module::t('list', 'Auto')
            ],
        ];
        $this->searchTypes = [
            self::SEARCH_TYPE_ORDER_ID => [
                'select_expression' => 'o.id',
                'title' => Module::t('list', 'Order ID')
            ],
            self::SEARCH_TYPE_LINK => [
                'select_expression' => 'o.link',
                'title' => Module::t('list', 'Link')
            ],
            self::SEARCH_TYPE_USERNAME => [
                'select_expression' => 'CONCAT(u.first_name, " ", u.last_name)',
                'title' => Module::t('list', 'Username')
            ],
        ];
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function getModes(): array
    {
        return $this->modes;
    }

    public function getSearchTypes(): array
    {
        return $this->searchTypes;
    }

    public function isSearchTypeValid($searchTypeCode): bool
    {
        return array_key_exists($searchTypeCode, $this->searchTypes);
    }

    public function isServiceValid($serviceId, array $services): bool
    {
        return array_key_exists($serviceId, $services);
    }

    public function getStatusCodeByName(string $name): ?int
    {
        foreach ($this->statuses as $code => $status) {
            if ($name === $status['status']) {
                return $code;
            }
        }
        return null;
    }

    public function getModeCodeByName(string $name): ?int
    {
        foreach ($this->modes as $code => $mode) {
            if ($name === $mode['mode']) {
                return $code;
            }
        }
        return null;
    }

    public function getOrdersQuery(
        ?int $statusCode = null,
        ?int $modeCode = null,
        ?int $serviceId = null,
        ?int $searchTypeCode = null,
        ?string $search = null
    ): Query {
        $query = (new Query())
            ->select([
                'o.id',
                'username' => 'CONCAT(u.first_name, " ", u.last_name)',
                'o.link',
                'o.quantity',
                'o.service_id',
                'service_name' => 's.name',
                'o.status',
                'o.mode',
                'created_at' => 'o.created_at',
            ])
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->innerJoin('services s', 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC])
        ;
        $this->addFiltersToQuery($query, $statusCode, $modeCode, $searchTypeCode, $search, $serviceId);
        return $query;
    }

    public function getServices(
        ?int $statusCode = null,
        ?int $modeCode = null,
        ?int $searchTypeCode = null,
        ?string $search = null
    ): array {
        $serviceOrdersQuery = (new Query())
            ->select([
                'o.service_id',
                'orders_number' => 'COUNT(*)',
            ])
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->groupBy('o.service_id')
        ;
        $this->addFiltersToQuery($serviceOrdersQuery, $statusCode, $modeCode, $searchTypeCode, $search);

        $query = (new Query())
            ->select([
                's.id',
                's.name',
                'orders_number' => 'IF(service_orders.orders_number IS NULL, 0, service_orders.orders_number)',
            ])
            ->from('services s')
            ->leftJoin(['service_orders' => $serviceOrdersQuery], 's.id = service_orders.service_id')
            ->orderBy(['orders_number' => SORT_DESC]);

        return $query->indexBy('id')->all();
    }

    public function getServicesTotalOrdersNumber(array $services): int
    {
        return array_reduce(
            $services,
            function ($totalOrdersNumber, $service) {
                return $totalOrdersNumber + $service['orders_number'];
            },
            0
        );
    }

    protected function addFiltersToQuery(
        Query $query,
        ?int $statusCode = null,
        ?int $modeCode = null,
        ?int $searchTypeCode = null,
        ?string $search = null,
        ?int $serviceId = null
    ): void {
        if (!is_null($statusCode)) {
            $query->andWhere('o.status=:status', [':status' => $statusCode]);
        }
        if (!is_null($modeCode)) {
            $query->andWhere('o.mode=:mode', [':mode' => $modeCode]);
        }
        if (!is_null($serviceId)) {
            $query->andWhere('s.id=:id', [':id' => $serviceId]);
        }
        if (!is_null($searchTypeCode) && $search) {
            $selectExpression = $this->searchTypes[$searchTypeCode]['select_expression'];
            $operation = static::SEARCH_TYPE_ORDER_ID == $searchTypeCode ? '=' : 'LIKE';
            $query->andWhere([$operation, $selectExpression, $search]);
        }
    }
}
