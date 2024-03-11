<?php

namespace app\modules\listing\models;

use app\modules\listing\Module;
use Yii;
use yii\db\Query;

class ListingOrder extends Order
{
    protected const int SEARCH_TYPE_ORDER_ID = 1;
    protected const int SEARCH_TYPE_LINK = 2;
    protected const int SEARCH_TYPE_USERNAME = 3;

    protected array $statuses;
    protected array $modes;
    protected array $searchTypes;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->statuses = [
            self::STATUS_CODE_PENDING => [
                'name' => 'pending',
                'title' => Module::t('list', 'Pending')
            ],
            self::STATUS_CODE_IN_PROGRESS => [
                'name' => 'inprogress',
                'title' => Module::t('list', 'In progress')
            ],
            self::STATUS_CODE_COMPLETED => [
                'name' => 'completed',
                'title' => Module::t('list', 'Completed')
            ],
            self::STATUS_CODE_CANCELED => [
                'name' => 'canceled',
                'title' => Module::t('list', 'Canceled')
            ],
            self::STATUS_CODE_ERROR => [
                'name' => 'error',
                'title' => Module::t('list', 'Error')
            ],
        ];
        $this->modes = [
            self::MODE_CODE_MANUAL => [
                'name' => 'manual',
                'title' => Module::t('list', 'Manual')
            ],
            self::MODE_CODE_AUTO => [
                'name' => 'auto',
                'title' => Module::t('list', 'Auto')
            ],
        ];
        $this->searchTypes = [
            self::SEARCH_TYPE_ORDER_ID => [
                'select_expression' => 'orders.id',
                'title' => Module::t('list', 'Order ID')
            ],
            self::SEARCH_TYPE_LINK => [
                'select_expression' => 'orders.link',
                'title' => Module::t('list', 'Link')
            ],
            self::SEARCH_TYPE_USERNAME => [
                'select_expression' => 'CONCAT(users.first_name, " ", users.last_name)',
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

    public function getStatusCodeByName(string $name): ?int
    {
        foreach ($this->statuses as $code => $status) {
            if ($name === $status['name']) {
                return $code;
            }
        }
        return null;
    }

    public function getModeCodeByName(string $name): ?int
    {
        foreach ($this->modes as $code => $mode) {
            if ($name === $mode['name']) {
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
        $query = static::createQuery()
            ->orderBy(['orders.id' => SORT_DESC])
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
        $query = static::createQuery()
            ->select([
                'services.id',
                'services.name',
                'orders_number' => 'COUNT(orders.id)'
            ])
            ->groupBy('services.id')
            ->orderBy(['orders_number' => SORT_DESC])
        ;
        $this->addFiltersToQuery($query, $statusCode, $modeCode, $searchTypeCode, $search);

        $servicesWithOrders = $query->indexBy('id')->all();
        $this->addServicesWithoutOrders($servicesWithOrders);

        return $servicesWithOrders;
    }

    protected static function createQuery(): Query
    {
        return static::find()
            ->joinWith('user', true, 'INNER JOIN')
            ->joinWith('service', true, 'INNER JOIN')
            ->asArray()
        ;
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
            $query->andWhere('orders.status=:status', [':status' => $statusCode]);
        }
        if (!is_null($modeCode)) {
            $query->andWhere('orders.mode=:mode', [':mode' => $modeCode]);
        }
        if (!is_null($serviceId)) {
            $query->andWhere('services.id=:id', [':id' => $serviceId]);
        }
        if (!is_null($searchTypeCode) && $search) {
            $selectExpression = $this->searchTypes[$searchTypeCode]['select_expression'];
            $operation = static::SEARCH_TYPE_ORDER_ID == $searchTypeCode ? '=' : 'LIKE';
            $query->andWhere([$operation, $selectExpression, $search]);
        }
    }

    private static function addServicesWithoutOrders(array &$servicesWithOrders): void
    {
        $allServices = Service::find()->indexBy('id')->all();
        foreach ($allServices as $id => $service) {
            if (!array_key_exists($id, $servicesWithOrders)) {
                $servicesWithOrders[$id] = [
                    'id' => $id,
                    'name' => $service['name'],
                    'orders_number' => 0,
                ];
            }
        }
    }
}
