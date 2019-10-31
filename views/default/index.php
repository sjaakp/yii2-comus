<?php

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
        'class' => 'mb-3 nav-justified'
    ]
]) ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'body',
            'content' => function($model, $key, $index, $column)    {
                return Html::a($model->body, [ '/' . $model->subject, '#' => 'cmt-' . $model->id ], [ 'data-pjax' => 0 ]);
            }
        ],
        [
            'attribute' => 'subject',
            'content' => function($model, $key, $index, $column)    {
                return Html::a($model->subject, [ '/' . $model->subject ], [ 'data-pjax' => 0 ]);
            }
        ],
        'created_at:datetime',
//        'updated_at',
        [
            'attribute' => 'created_by',
            'content' => function($model, $key, $index, $column) use ($module)   {
                return Html::a($module->getNickname($model->createdBy), '#', [ 'data-pjax' => 0 ]);
            }
        ],
//        'updated_by',
//        'status',

        [
            'class' => 'yii\grid\ActionColumn',
/*            'visibleButtons' => [
                'view' => ! Yii::$app->user->isGuest,
                'update' => function ($model, $key, $index) {
                    return Yii::$app->user->can('updateItem', $model);
                },
                'delete' => Yii::$app->user->can('deleteItem')
            ]*/
        ],
    ],
    'tableOptions' => ['class' => 'table table-sm table-bordered'],
    'formatter' => [
        'class' => Formatter::class,
        'datetimeFormat' => 'short'
    ]
]); ?>

<?php Pjax::end() ?>