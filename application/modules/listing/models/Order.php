<?php

namespace app\modules\listing\models;

use yii\db\ActiveRecord;

class Order extends ActiveRecord
{
    public const STATUS_CODE_PENDING = 0;
    public const STATUS_CODE_IN_PROGRESS = 1;
    public const STATUS_CODE_COMPLETED = 2;
    public const STATUS_CODE_CANCELED = 3;
    public const STATUS_CODE_ERROR = 4;
    public const MODE_CODE_MANUAL = 0;
    public const MODE_CODE_AUTO = 1;

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
}
