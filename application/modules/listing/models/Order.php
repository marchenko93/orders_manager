<?php

namespace app\modules\listing\models;

use yii\db\ActiveRecord;
use yii\db\Query;

class Order extends ActiveRecord
{
    private const STATUS_CODE_PENDING = 0;
    public const STATUS_CODE_IN_PROGRESS = 1;
    public const STATUS_CODE_COMPLETED = 2;
    public const STATUS_CODE_CANCELED = 3;
    public const STATUS_CODE_ERROR = 4;
    public const STATUSES = [
        self::STATUS_CODE_PENDING => ['name' => 'pending', 'title' => 'Pending'],
        self::STATUS_CODE_IN_PROGRESS => ['name' => 'inprogress', 'title' => 'In progress'],
        self::STATUS_CODE_COMPLETED => ['name' => 'completed', 'title' => 'Completed'],
        self::STATUS_CODE_CANCELED => ['name' => 'canceled', 'title' => 'Canceled'],
        self::STATUS_CODE_ERROR => ['name' => 'error', 'title' => 'Error'],
    ];
    public const MODE_CODE_MANUAL = 0;
    public const MODE_CODE_AUTO = 1;
    public const MODES = [
        self::MODE_CODE_MANUAL => ['name' => 'manual', 'title' => 'Manual'],
        self::MODE_CODE_AUTO => ['name' => 'auto', 'title' => 'Auto'],
    ];

    public static function tableName()
    {
        return 'orders';
    }

    public static function getStatusCodeByName(string $name): ?int
    {
        foreach (self::STATUSES as $code => $status) {
            if ($name === $status['name']) {
                return $code;
            }
        }
        return null;
    }

    public static function getModeCodeByName(string $name): ?int
    {
        foreach (self::MODES as $code => $mode) {
            if ($name === $mode['name']) {
                return $code;
            }
        }
        return null;
    }

    public static function getOrdersQuery(?int $statusCode = null, ?int $modeCode = null)
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
        if (!is_null($modeCode)) {
            $query->andWhere('o.mode=:mode', [':mode' => $modeCode]);
        }

        return $query;
    }
}