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

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use sjaakp\comus\Module;
use sjaakp\comus\models\Comment;

/** @var $module Module */
/** @var $comment Comment */
/** @var $action string action in DefaultController */
/** @var $class string */
/** @var $label string */
/** @var $avatar string */
/** @var $placeholder false|string */
/** @var $this yii\web\View */

// @link https://stackoverflow.com/questions/4954252/css-textarea-that-expands-as-you-type-text
$this->registerJs('
$("textarea").on("focus paste input", function () {
    if ($(this).outerHeight() > this.scrollHeight){
        $(this).height(1)
    }
    while ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))){
        $(this).height($(this).height() + 1)
    }
});
');

$url = ["/{$module->id}/default/$action"];
if (! $comment->isNewRecord) $url['id'] = $comment->id;

$form = ActiveForm::begin([
    'action' => $url,
    'options' => [
        'class' => $class,
        'data-pjax' => true,
    ]
]);

if (! empty($avatar))   {
    echo Html::tag('div', $avatar, [ 'class' => 'comus-avatar' ]);
}

echo $form->field($comment, 'body', [
    'options' => [
        'class' => 'comus-group'
    ]
])->label(isset($label) ? $label : $module->getNickname(null, false) . ':')->textarea([
    'class' => 'form-control',
    'rows' => 1,
    'placeholder' => $placeholder
]);

echo Html::activeHiddenInput($comment, 'subject');
echo Html::activeHiddenInput($comment, 'parent');

echo Html::submitButton($module->icons['send'], [
    'class' => 'btn btn-outline-success',
    'title' => Yii::t('comus', 'Send')
]);

ActiveForm::end();
?>
