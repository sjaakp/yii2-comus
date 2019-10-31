<?php

use yii\widgets\ListView;
use sjaakp\comus\models\Comment;

/** @var $provider yii\data\ActiveDataProvider */
/** @var $module sjaakp\comment\Module */
/** @var $baseId string */
/** @var $level int */
/** @var $this yii\web\View */

$statuses = [
    Comment::PENDING => 'pending',
    Comment::ACCEPTED => 'accepted',
    Comment::REJECTED => 'rejected'
];

if ($provider->count > 0) echo ListView::widget([
    'dataProvider' => $provider,
    'options' => [
        'tag' => 'ol',
        'class' => 'comus-list list-unstyled'
    ],
    'itemView' => '_item',
    'itemOptions' => function($model, $key, $index, $widget) use($statuses)   {
        return [
            'tag' => 'li',
            'class' => 'comus-' . $statuses[$model->status],
            'id' => 'cmt-' . $model->id
        ];
    },
    'viewParams' => [
        'module' => $module,
        'level' => $level
    ],
    'summary' => '',
]);
?>
