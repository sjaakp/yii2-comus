<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use sjaakp\comus\CommentList;

/** @var $comment sjaakp\comus\models\Comment */
/** @var $count int */
/** @var $module sjaakp\comus\Module */
/** @var $options array */
/** @var $this yii\web\View */

$baseId = str_replace('/', '-', $comment->subject);
$user = Yii::$app->user;

$statuses = [
    $comment::PENDING => 'pending',
    $comment::ACCEPTED => 'accepted',
    $comment::REJECTED => 'rejected'
];

$this->registerJs('
    $(".comus-block").change(function(e) {
        $(e.target).parent().submit();
    });
    $(".comus-edit").click(function(e) {
        e.preventDefault();
        $(this).closest("li").toggleClass("comus-editing").removeClass("comus-replying");
    });
    $(".comus-reply").click(function(e) {
        e.preventDefault();
        $(this).closest("li").toggleClass("comus-replying");
    });
    $(".comus-editor").submit(function(e) {
        $(this).parent().removeClass("comus-editing");
    });
    $(".comus-comment form").submit(function(e) {
        $(this).parent().removeClass("comus-replying");
    });
');

Html::addCssClass($options, 'comus-block');

Pjax::begin([
    'timeout' => 20000,
    'enablePushState' => false,
    'options' => $options
]);

echo Html::tag('h3', Yii::t('comus', 'Comments ({n,number})', [
    'n' => $count
]), [ 'class' => 'comus-summary']);

echo CommentList::widget([
    'subject' => $comment->subject,
    'module' => $module,
]);

if ($user->isGuest) {
    echo Html::tag('p', $module->loginPrompt, [ 'class' => 'comus-prompt' ]);
}
else    {
    echo $this->render('_editor', [
        'module' => $module,
        'comment' => $comment,
        'action' => 'create',
        'class' => 'comus-create',
        'placeholder' => Yii::t('comus', 'My comment...')
    ]);
}
Pjax::end();
