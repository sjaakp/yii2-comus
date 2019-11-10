<?php

namespace sjaakp\comus;

use yii\web\AssetBundle;

class CountAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';

    public $css = [
        'comus-count.css'
    ];
    public $depends = [
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
}
