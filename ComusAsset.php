<?php

namespace sjaakp\comus;

use yii\web\AssetBundle;

class ComusAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';

    public $css = [
        'comus.css'
    ];
    public $depends = [
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
}
