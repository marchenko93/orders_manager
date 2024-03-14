<?php

namespace orders_list\models;

use yii\db\ActiveRecord;

class Order extends ActiveRecord
{
    public const int STATUS_CODE_PENDING = 0;
    public const int STATUS_CODE_IN_PROGRESS = 1;
    public const int STATUS_CODE_COMPLETED = 2;
    public const int STATUS_CODE_CANCELED = 3;
    public const int STATUS_CODE_ERROR = 4;
    public const int MODE_CODE_MANUAL = 0;
    public const int MODE_CODE_AUTO = 1;

    public static function tableName()
    {
        return 'orders';
    }
}
