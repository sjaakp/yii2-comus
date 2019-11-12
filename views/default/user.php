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

use sjaakp\comus\Module;
use sjaakp\comus\UserComments;

/* @var $this yii\web\View */
/* @var $identity yii\db\ActiveRecord */
/* @var $module Module */

$this->title = $module->getNickname($identity, false);
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= $this->title ?></h1>

<?= UserComments::widget([
     'userId' => $identity->id
]) ?>
