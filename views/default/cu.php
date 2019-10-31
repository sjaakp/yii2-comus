<?php

use sjaakp\comus\Comment;

/* @var $subject string */
/* @var $moduleId string */
?>

<?= Comment::widget([
    'subject' => $subject,
    'moduleId' => $moduleId
]) ?>
