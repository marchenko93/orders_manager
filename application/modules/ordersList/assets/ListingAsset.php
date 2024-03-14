<?php

namespace ordersList\assets;

use yii\web\AssetBundle;

class ListingAsset extends AssetBundle
{
    public $sourcePath = '@ordersList/resources';
    public $css = [
        'css/bootstrap.min.css',
        'css/custom.css',
    ];
    public $js = [
        'js/jquery.min.js',
        'js/bootstrap.min.js',
    ];
}
