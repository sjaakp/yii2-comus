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
use sjaakp\comus\ComusList;
use sjaakp\comus\models\Comment;

/** @var $model Comment */
/** @var $module sjaakp\comus\Module */
/** @var $level int */
/** @var $this yii\web\View */

$delLabel = Yii::t('comus', 'Delete');
$editLabel = Yii::t('comus', 'Edit');
$replyLabel = Yii::t('comus', 'Reply');
$nextLabel = Yii::t('comus', 'Next');
$previousLabel = Yii::t('comus', 'Previous');

$reply = new Comment([
    'subject' => $model->subject,
    'parent' => $model->id
]);

$identity = $model->createdBy;
$avatar = $module->getAvatar($identity);
$moduleId = $module->id;
$user = Yii::$app->user;

$meta = Html::tag('div', $module->getNickname($identity), [ 'class' => 'comus-author' ])
    . Html::tag('div', $model->getFormattedTime($module->datetimeFormat), [ 'class' => 'comus-date' ]);

$ribbon = Html::tag('div', $meta, [ 'class' => 'comus-meta' ]);

if ($module->userCanComment())   {
    $buttons = '';
    if ($user->can('manageComments'))    {
        $buttons = Html::beginForm(["/$moduleId/default/update", 'id' => $model->id], 'post', [
            'class' => 'comus-status form-inline',
            'data-pjax' => true
        ]);

        $valAcc = Comment::ACCEPTED;
        $valRej = Comment::REJECTED;
        $idAcc = "i{$model->id}-$valAcc";
        $idRej = "i{$model->id}-$valRej";
        $statusButtons = Html::tag('div', Html::radio('Comment[status]', $model->status == $valAcc, [
                'id' => $idAcc,
                'value' => $valAcc
            ]) . Html::label($module->icons['accept'], $idAcc), [
                'class' => 'comus-status-item',
                'title' => Yii::t('comus', 'Accept')
            ])
            . Html::tag('div', Html::radio('Comment[status]', $model->status == $valRej, [
                'id' => $idRej,
                'value' => $valRej
            ]) . Html::label($module->icons['reject'], $idRej), [
                'class' => 'comus-status-item',
                'title' => Yii::t('comus', 'Reject')
            ]);

        $buttons .= Html::tag('div', $statusButtons, [ 'class' => 'form_group field-comment-status' ]);
        $buttons .= Html::endForm();
        if ($model->status == Comment::PENDING) {
            $buttons .= Html::a($module->icons['previous'], [ "/$moduleId/default/previous", 'before' => $model->created_at ], [
                'class' => 'comus-previous',
                'title' => $previousLabel,
                'aria-label' => $previousLabel,
                'data-pjax' => 0,
            ]);
            $buttons .= Html::a($module->icons['next'], [ "/$moduleId/default/next", 'after' => $model->created_at ], [
                'class' => 'comus-next',
                'title' => $nextLabel,
                'aria-label' => $nextLabel,
                'data-pjax' => 0,
            ]);
        }
    }
    if ($user->can('updateComment', $model))    {
        $buttons .= Html::a($module->icons['edit'], '#', [
            'class' => 'comus-edit',
            'title' => $editLabel,
            'aria-label' => $editLabel,
            'data-pjax' => 0,
        ]);
    }
    if ($user->can('deleteComment', $model))    {
        $buttons .= Html::a($module->icons['delete'], ["/$moduleId/default/delete", 'id' => $model->id], [
            'class' => 'comus-delete',
            'title' => $delLabel,
            'aria-label' => $delLabel,
            'data' => [
                'pjax' => true,
                'pjax-scrollto' => false,
                'confirm' => Yii::t('comus', 'Deleting {nick}\'s Comment. Are you sure?', [
                    'nick' => $module->getNickname($identity, false)
                ]),
                'method' => 'post'
            ]
        ]);
    }
    if ($level < $module->maxLevel)    {
        $buttons .= Html::a($module->icons['reply'], '#', [
            'class' => 'comus-reply',
            'title' => $replyLabel,
            'aria-label' => $replyLabel,
            'data-pjax' => 0,
        ]);
    }
    $ribbon .= Html::tag('div', $buttons, [ 'class' => 'comus-buttons' ]);
}

$inner = Html::tag('div', $ribbon, [ 'class' => 'comus-ribbon' ])
    . $model->getSanitizedBody();

if ($user->can('updateComment')) {
    $inner .= $this->render('_editor', [
        'module' => $module,
        'comment' => $model,
        'action' => 'update',
        'class' => 'comus-editor',
        'label' => Yii::t('comus', 'Update'),
        'avatar' => null,
        'placeholder' => false
    ]);
}

$wrap = empty($avatar) ? '' : Html::tag('div', $avatar, [ 'class' => 'comus-avatar' ]);
$wrap .= Html::tag('div', $inner, [ 'class' => 'comus-inner' ]);

echo Html::tag('div', $wrap, [
    'class' => 'comus-wrap ' . $model->classes,
    'tabindex' => 0
]);

if ($level < $module->maxLevel)    {
    $level1 = $level + 1;
    $edt = $module->userCanComment() ? Html::tag('div',
        $this->render('_editor', [
            'module' => $module,
            'comment' => $reply,
            'action' => 'create',
            'class' => 'pb-2 border-bottom',
            'avatar' => $module->getAvatar(),
            'placeholder' => Yii::t('comus', 'My reply...')
        ]),  [ 'class' => 'comus-comment' ]) : '';

    $lst = ComusList::widget([
        'subject' => $model->subject,
        'module' => $module,
        'parent' => $model->id,
        'level' => $level1
    ]);

    $lvl = $module->orderDescending ? $edt . $lst : $lst . $edt;

    echo Html::tag('div', $lvl, [ 'class' => "comus-level comus-level-$level1" ]);
}
?>
