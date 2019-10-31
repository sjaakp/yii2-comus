<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use sjaakp\comus\Module;
use sjaakp\comus\models\Comment;

/** @var $module Module */
/** @var $comment Comment */
/** @var $action string action in DefaultController */
/** @var $class string */
/** @var $label string */
/** @var $placeholder false|string */

$form = ActiveForm::begin([
    'action' => ["/{$module->id}/default/$action"],
    //    'enableClientValidation' => false,
    //    'validateOnChange' => false,
    //    'validateOnBlur' => false,
    'options' => [
        'class' => $class,
        'data-pjax' => true,
    ]
]);

echo $form->field($comment, 'body', [
    'options' => [
        'class' => 'comus-group'
    ]
])->label(isset($label) ? $label : $module->getNickname() . ':')->textarea([
    'class' => 'form-control',
    'placeholder' => $placeholder
]);

echo Html::activeHiddenInput($comment, 'subject');
echo Html::activeHiddenInput($comment, 'parent');

echo Html::submitButton(Yii::t('comus', 'Save'), ['class' => 'btn btn-outline-success']);

ActiveForm::end();
