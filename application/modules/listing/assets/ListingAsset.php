<?php

namespace app\modules\listing\assets;

use yii\web\AssetBundle;

class ListingAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/listing/resources';
    public $css = [
        'css/bootstrap.min.css',
        'css/custom.css',
    ];
    public $js = [
        'js/bootstrap.min.js',
        'js/jquery.min.js',
    ];
}
