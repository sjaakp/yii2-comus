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

use yii\helpers\StringHelper;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\i18n\Formatter;
use yii\bootstrap4\Tabs;
use sjaakp\comus\Module;
use sjaakp\comus\models\Comment;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $category int */
/* @var $module Module */

$this->title = Yii::t('comus', 'Comments');
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= $this->title ?></h1>

<?php Pjax::begin([
    'enablePushState' => false
]) ?>

<?= Tabs::widget([
    'items' => [
        [
            'label' => Yii::t('comus', 'Pending'),
            'url' => ['index', 's' => Comment::PENDING],
            'active' => $category == Comment::PENDING
        ],
        [
            'label' => Yii::t('comus', 'Accepted'),
            'url' => ['index', 's' => Comment::ACCEPTED],
            'active' => $category == Comment::ACCEPTED
        ],
        [
            'label' => Yii::t('comus', 'Rejected'),
            'url' => ['index', 's' => Comment::REJECTED],
            'active' => $category == Comment::REJECTED
        ],
    ],
    'options' => [
        'class' => 'mb-3 nav-justified font-weight-bold'
    ]
]) ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'body',
            'content' => function($model, $key, $index, $column) use ($module)    {
                return Html::a(StringHelper::truncate($model->body, $module->truncLength), $model->href, [ 'data-pjax' => 0 ]);
            },
        ],
        [
            'attribute' => 'subject',
            'content' => function($model, $key, $index, $column)    {
                return Html::a($model->subject, [ '/' . $model->subject ], [ 'data-pjax' => 0 ]);
            },
            'contentOptions' => [ 'class' => 'small' ]
        ],
        [
            'attribute' => 'created_at',
            'format' => 'datetime',
            'contentOptions' => [ 'class' => 'small' ]
        ],
        [
            'attribute' => 'created_by',
            'content' => function($model, $key, $index, $column) use ($module)   {
                return $module->getNickname($model->createdBy, [ 'data-pjax' => 0 ]);
            },
            'contentOptions' => [ 'class' => 'small' ]
        ],
    ],
    'tableOptions' => ['class' => 'table table-sm table-bordered'],
    'formatter' => [
        'class' => Formatter::class,
        'datetimeFormat' => 'short'
    ],
    'summary' => Yii::t('comus', '{begin}-{end}/{totalCount}'),
    'emptyText' => Yii::t('comus', 'Empty')
]); ?>

<?php Pjax::end() ?>
