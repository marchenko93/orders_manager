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
        self::SEARCH_TYPE_ORDER_ID => ['name' => 'o.id', 'title' => 'Order ID'],
        self::SEARCH_TYPE_LINK => ['name' => 'o.link', 'title' => 'Link'],
        self::SEARCH_TYPE_USERNAME => ['name' => 'CONCAT(u.first_name, " ", u.last_name)', 'title' => 'Username'],
    ];

    public static function tableName()
    {
        return 'orders';
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

    public static function getServices(?int $statusCode = null, ?int $modeCode = null): array
    {
        $query = new Query();
        $joinCondition = 's.id = o.service_id';
        $joinConditionParams = [];
        if (!is_null($statusCode)) {
            $joinCondition .= ' AND o.status = :status';
            $joinConditionParams[':status'] = $statusCode;
        }
        if (!is_null($modeCode)) {
            $joinCondition .= ' AND o.mode = :mode';
            $joinConditionParams[':mode'] = $modeCode;
        }

        $query->select([
            's.id',
            's.name',
            'COUNT(o.id) orders_number',
        ])
            ->from('services s')
            ->leftJoin('orders o', $joinCondition, $joinConditionParams)
            ->groupBy('s.id, s.name')
            ->orderBy(['orders_number' => SORT_DESC])
        ;

        return $query->indexBy('id')->all();
    }

    public static function getOrdersQuery(?int $statusCode = null)
    {
        $query = new Query();
        $query->select([
            'o.id',
            'user' => 'CONCAT(u.first_name, " ", u.last_name)',
            'o.link',
            'o.quantity',
            'o.service_id',
            'service_name' => 's.name',
            'o.status',
            'o.mode',
            'created_date' => 'FROM_UNIXTIME(o.created_at, "%Y-%m-%d")',
            'created_time' => 'FROM_UNIXTIME(o.created_at, "%h:%i:%s")',
        ])
            ->from('orders o')
            ->innerJoin('users u', 'o.user_id = u.id')
            ->innerJoin('services s', 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC])
        ;
        if (!is_null($statusCode)) {
            $query->andWhere('o.status=:status', [':status' => $statusCode]);
        }
        return $query;
    }

    public static function getFilterOrdersQuery(?int $statusCode = null, ?int $modeCode = null, ?int $serviceId = null)
    {
        $query = static::getOrdersQuery($statusCode);
        if (!is_null($modeCode)) {
            $query->andWhere('o.mode=:mode', [':mode' => $modeCode]);
        }
        if (!is_null($serviceId)) {
            $query->andWhere('s.id=:id', [':id' => $serviceId]);
        }

        return $query;
    }

    public static function getSearchOrdersQuery(int $searchTypeCode, string $search, ?int $statusCode = null)
    {
        $query = static::getOrdersQuery($statusCode);
        $searchType = static::SEARCH_TYPES[$searchTypeCode]['name'];
        $operation = static::SEARCH_TYPE_ORDER_ID == $searchTypeCode ? '=' : 'LIKE';
        $query->andWhere([$operation, $searchType, $search]);
        return $query;
    }
}