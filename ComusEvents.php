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

/**
 *  This is a CODE TEMPLATE to intercept the creation of a new Comment in sjaakp/yii2-comus.
 *  It makes use of yii's normal ActiveRecord events.
 *  To hook it up, put the fully classified classname in the 'bootstrap' part of the main configuration file, like so:
 *
 *  $config = [
 *      'id' => 'testsite',
 *      // ... other settings ...
 *      'bootstrap' => [
 *          'app\extensions\ComusEvents',
 *          'comment',
 *          // ... perhaps some other bootstrap components...
 *      ],
 *      'components' => [
 *          // ... lots of components ...
 *      ],
 *      'modules' => [
 *          'comment' => [
 *              'class' => 'sjaakp\comus\Module',
 *              'loginUrl' => '/site/login',
 *              // ... other module settings ...
 *          ],
 *          // ... more modules ...
 *      ],
 *  ];
 *
 *  Other events can be intercepted in the same way.
 *  @see https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_DELETE-detail
 */

namespace app\extensions;

use Yii;
use yii\base\Component;
use yii\db\AfterSaveEvent;
use yii\base\BootstrapInterface;
use sjaakp\comus\models\Comment;

class ComusEvents extends Component implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        AfterSaveEvent::on(Comment::class, Comment::EVENT_AFTER_INSERT, [ $this, 'handler' ]);
    }

    /**
     * @param $event AfterSaveEvent
     */
    public static function handler($event)
    {
        /** @var $comment Comment */
        $comment = $event->sender;
        $comment->refresh();    // to update created_at
        $commenter = $comment->createdBy;

        /**
         * Do whatever you like with the just created comment.
         * For instance, notify the moderator or the author of the subject by e-mail.
         *
         * Here, as an example the new comment is logged.
         * Notice that for this to work as expected, the application's 'log' component
         *  has to be configured properly.
         * @link https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging
         */
        Yii::info([
            'posted at' => $comment->created_at,
            'posted by' => $commenter->name,    // or any other attribute holding the user's name
            'content' => $comment->body
        ], 'comus');
    }
}
