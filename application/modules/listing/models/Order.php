<?php

namespace app\modules\listing\models;

use yii\db\ActiveRecord;
use yii\db\Query;

class Order extends ActiveRecord
{
    protected const STATUS_CODE_PENDING = 0;
    protected const STATUS_CODE_IN_PROGRESS = 1;
    protected const STATUS_CODE_COMPLETED = 2;
    protected const STATUS_CODE_CANCELED = 3;
    protected const STATUS_CODE_ERROR = 4;
    protected const STATUSES = [
        self::STATUS_CODE_PENDING => ['name' => 'pending', 'title' => 'Pending'],
        self::STATUS_CODE_IN_PROGRESS => ['name' => 'inprogress', 'title' => 'In progress'],
        self::STATUS_CODE_COMPLETED => ['name' => 'completed', 'title' => 'Completed'],
        self::STATUS_CODE_CANCELED => ['name' => 'canceled', 'title' => 'Canceled'],
        self::STATUS_CODE_ERROR => ['name' => 'error', 'title' => 'Error'],
    ];
    protected const MODE_CODE_MANUAL = 0;
    protected const MODE_CODE_AUTO = 1;
    protected const MODES = [
        self::MODE_CODE_MANUAL => ['name' => 'manual', 'title' => 'Manual'],
        self::MODE_CODE_AUTO => ['name' => 'auto', 'title' => 'Auto'],
    ];
    protected const SEARCH_TYPE_ORDER_ID = 1;
    protected const SEARCH_TYPE_LINK = 2;
    protected const SEARCH_TYPE_USERNAME = 3;
    protected const SEARCH_TYPES = [
        self::SEARCH_TYPE_ORDER_ID => ['select_expression' => 'orders.id', 'title' => 'Order ID'],
        self::SEARCH_TYPE_LINK => ['select_expression' => 'orders.link', 'title' => 'Link'],
        self::SEARCH_TYPE_USERNAME => [
            'select_expression' => 'CONCAT(users.first_name, " ", users.last_name)',
            'title' => 'Username'
        ],
    ];

    public static function tableName()
    {
        return 'orders';
    }

    public function getService()
    {
        return $this->hasOne(Service::class, ['id' => 'service_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function getStatusCodeByName(string $name): ?int
    {
        foreach (static::STATUSES as $code => $status) {
            if ($name === $status['name']) {
                return $code;
            }
        }
        return null;
    }

    public static function getModeCodeByName(string $name): ?int
    {
        foreach (static::MODES as $code => $mode) {
            if ($name === $mode['name']) {
                return $code;
            }
        }
        return null;
    }

    public static function getStatuses(): array
    {
        return static::STATUSES;
    }

    public static function getModes(): array
    {
        return static::MODES;
    }

    public static function getSearchTypes(): array
    {
        return static::SEARCH_TYPES;
    }

    public static function getOrdersQuery(
        ?int $statusCode = null,
        ?int $modeCode = null,
        ?int $serviceId = null,
        ?int $searchTypeCode = null,
        ?string $search = null
    ): Query {
        $query = static::createQuery()
            ->orderBy(['orders.id' => SORT_DESC])
        ;
        static::addFiltersToQuery($query, $statusCode, $modeCode, $searchTypeCode, $search, $serviceId);
        return $query;
    }

    public static function getServices(
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
        static::addFiltersToQuery($query, $statusCode, $modeCode, $searchTypeCode, $search);

        $servicesWithOrders = $query->indexBy('id')->all();
        static::addServicesWithoutOrders($servicesWithOrders);

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

    protected static function addFiltersToQuery(
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
            $selectExpression = static::SEARCH_TYPES[$searchTypeCode]['select_expression'];
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
