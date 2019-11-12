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

use sjaakp\comus\Comment;

/* @var $subject string */
/* @var $moduleId string */
?>

<?= Comment::widget([
    'subject' => $subject,
    'moduleId' => $moduleId
]) ?>
