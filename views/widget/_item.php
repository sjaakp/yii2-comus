<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use sjaakp\comus\CommentList;
use sjaakp\comus\models\Comment;

/** @var $model Comment */
/** @var $module sjaakp\comus\Module */
/** @var $level int */
/** @var $this yii\web\View */

$statuses = [
    $model::ACCEPTED => '<i class="far fa-comment-check" title="Accept"></i>',
    $model::REJECTED => '<i class="far fa-hand-paper" title="Reject"></i>',
];

$delLabel = Yii::t('comus', 'Delete');
$editLabel = Yii::t('comus', 'Edit');
$replyLabel = Yii::t('comus', 'Reply');

$reply = new Comment([
    'subject' => $model->subject,
    'parent' => $model->id
]);

$identity = $model->createdBy;
$moduleId = $module->id;
$user = Yii::$app->user;

$meta = Html::tag('div', Html::a($module->getNickname($identity), ['/profile/view', 'id' => $identity->id ]), [ 'class' => 'comus-author' ])
    . Html::tag('div', Yii::$app->formatter->asRelativeTime($model->created_at), [ 'class' => 'comus-date' ]);

if ($module->userCanComment())   {
    $buttons = '';
    if ($user->can('acceptComment'))    {
        $buttons = Html::beginForm(["/$moduleId/default/update", 'id' => $model->id], 'post', [
            'class' => 'form-inline',
            'data-pjax' => true,
        ]);
        $statusButtons = '';
        foreach ($statuses as $val => $lbl)  {
            $id = "i{$model->id}-$val";
            $statButton = Html::radio('Comment[status]', $val == $model->status, [
                'id' => $id,
                'value' => $val
            ])
                . Html::label($lbl, $id);
            $statusButtons .= Html::tag('div', $statButton, [ 'class' => 'comus-status-item']);
        }
        $buttons .= Html::tag('div', $statusButtons, [ 'class' => 'form_group field-comment-status' ]);
        $buttons .= Html::endForm();
    }
    if ($user->can('updateComment', $model))    {
        $buttons .= Html::a('<i class="far fa-comment-alt-edit"></i>', '#', [
            'class' => 'comus-edit',
            'title' => $editLabel,
            'aria-label' => $editLabel,
            'data-pjax' => 0,
        ]);
    }
    if ($user->can('deleteComment', $model))    {
        $buttons .= Html::a('<i class="far fa-trash-alt"></i>', ["/$moduleId/default/delete", 'id' => $model->id], [
            'class' => 'comus-delete',
            'title' => $delLabel,
            'aria-label' => $delLabel,
            'data' => [
                'pjax' => true,
                'confirm' => Yii::t('comus', 'Deleting Comment. Are you sure?'),
                'method' => 'post'
            ]
        ]);
    }
    $buttons .= Html::a('<i class="far fa-reply"></i>', '#', [
        'class' => 'comus-reply',
        'title' => $replyLabel,
        'aria-label' => $replyLabel,
        'data-pjax' => 0,
    ]);
    $meta .= Html::tag('div', $buttons, [ 'class' => 'comus-buttons' ]);
}

$wrap = Html::tag('div', $meta, [ 'class' => 'comus-meta' ])
    . Html::tag('div', $model->body, [ 'class' => 'comus-body' ]);

if ($user->can('updateComment')) {
    $wrap .= $this->render('_editor', [
        'module' => $module,
        'comment' => $model,
        'action' => 'update',
        'class' => 'comus-editor',
        'label' => Yii::t('comus', 'Update'),
        'placeholder' => false
    ]);
}

echo Html::tag('div', $wrap, [ 'class' => 'comus-wrap' ]);

if ($level <= $module->maxLevel)    {
    echo CommentList::widget([
        'subject' => $model->subject,
        'module' => $module,
        'parent' => $model->id,
        'level' => $level + 1
    ]);

    if ($module->userCanComment())   {
        echo Html::tag('div',
            $this->render('_editor', [
                'module' => $module,
                'comment' => $reply,
                'action' => 'create',
                'class' => false,
                'placeholder' => Yii::t('comus', 'My reply...')
            ]),  [ 'class' => 'comus-comment' ]);
    }
}
?>
