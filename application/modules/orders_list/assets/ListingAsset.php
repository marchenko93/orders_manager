<?php

namespace app\modules\orders_list\assets;

use yii\web\AssetBundle;

class ListingAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/orders_list/resources';
    public $css = [
        'css/bootstrap.min.css',
        'css/custom.css',
    ];
    public $js = [
        'js/jquery.min.js',
        'js/bootstrap.min.js',
    ];
}
