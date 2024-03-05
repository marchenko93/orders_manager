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
        self::STATUS_CODE_PENDING => ['slug' => 'pending', 'title' => 'Pending'],
        self::STATUS_CODE_IN_PROGRESS => ['slug' => 'inprogress', 'title' => 'In progress'],
        self::STATUS_CODE_COMPLETED => ['slug' => 'completed', 'title' => 'Completed'],
        self::STATUS_CODE_CANCELED => ['slug' => 'canceled', 'title' => 'Canceled'],
        self::STATUS_CODE_ERROR => ['slug' => 'error', 'title' => 'Error'],
    ];

    public static function tableName()
    {
        return 'orders';
    }

    public static function getStatusCodeBySlug(string $slug): ?int
    {
        foreach (self::STATUSES as $code => $status) {
            if ($slug === $status['slug']) {
                return $code;
            }
        }
        return null;
    }

    public static function getOrdersQuery(?int $status = null)
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
        if (!is_null($status)) {
            $query->where('o.status=:status', [':status' => $status]);
        }

        return $query;
    }
}