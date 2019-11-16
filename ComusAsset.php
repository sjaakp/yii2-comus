<?php

/**
 * sjaakp/yii2-comus
 * ----------
 * Comment module for Yii2 framework
 * Version 1.0.0
 * Copyright (c) 2019
 * Sjaak Priester, Amsterdam
 * MIT License
 * https://github.com/sjaakp/yii2-comus
 * https://sjaakpriester.nl
 */

namespace sjaakp\comus;

use yii\web\AssetBundle;

/**
 * Class ComusAsset
 * @package sjaakp\comus
 */
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
