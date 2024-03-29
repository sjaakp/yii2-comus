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

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use sjaakp\comus\ComusList;

/** @var $comment sjaakp\comus\models\Comment */
/** @var $count int */
/** @var $module sjaakp\comus\Module */
/** @var $options array */
/** @var $this yii\web\View */

$this->registerJs('
    function setLiClass(li, c) {
        let b = li.hasClass(c);
        $(".comus-block li").removeClass("comus-editing comus-replying");
        if (!b) li.addClass(c);
    }
    $(".comus-block").on("change", ".comus-status", function(e) {
        e.preventDefault();
        $(this).submit();
    })
    .on("click", ".comus-edit", function(e) {
        e.preventDefault();
        let li = $(this).closest("li");
        setLiClass(li, "comus-editing");
        li.find(".comus-editor textarea").focus();
    })
    .on("click", ".comus-reply", function(e) {
        e.preventDefault();
        let li = $(this).closest("li");
        setLiClass(li, "comus-replying");
        li.find(".comus-comment textarea").focus();
    })
    .on("submit", ".comus-editor", function(e) {
        $(this).parent().removeClass("comus-editing");
    })
    .on("submit", ".comus-comment form", function(e) {
        $(this).parent().removeClass("comus-replying");
    });
');

Yii::$app->user->setReturnUrl(Yii::$app->request->url);

$cls = 'comus-block comus-' . ($module->orderDescending ? 'desc' : 'asc');
Html::addCssClass($options, $cls);

Pjax::begin([
    'enablePushState' => false,
    'options' => $options,
    'id' => 'cmts'
]);

echo Html::tag('h3', Yii::t('comus', 'Comments ({n,number})', [
    'n' => $count
]), [ 'class' => 'comus-summary']);

if ($module->userCanView()) {

    $edt = $module->userCanComment() ? $this->render('_editor', [
        'module' => $module,
        'comment' => $comment,
        'action' => 'create',
        'class' => 'comus-create',
        'avatar' => $module->getAvatar(),
        'placeholder' => Yii::t('comus', 'My comment...')
    ]) : Html::tag('p', Yii::t('comus', '<a href="{loginUrl}">Login</a> to comment', [
        'loginUrl' => Url::to($module->loginUrl)
    ]), [ 'class' => 'comus-prompt' ]);

    $lst = ComusList::widget([
        'subject' => $comment->subject,
        'module' => $module,
    ]);

    $lvl = $module->orderDescending ? $edt . $lst : $lst . $edt;

    echo Html::tag('div', $lvl, [ 'class' => 'comus-level comus-level-0' ]);
}
else    {
    echo Html::tag('p',  Yii::t('comus', '<a href="{loginUrl}">Login</a> to view comments', [
        'loginUrl' => Url::to($module->loginUrl)
    ]), [ 'class' => 'comus-prompt' ]);
}
Pjax::end();
