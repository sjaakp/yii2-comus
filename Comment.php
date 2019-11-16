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

namespace sjaakp\comus;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use sjaakp\comus\models\Comment as CommentModel;

/**
 * Class Comment
 * @package sjaakp\comus
 */
class Comment extends ComusBase
{
    /**
     * @var array HTML options for the surrounding div
     */
    public $options = [];

    /**
     * @var int start value of widget counter
     * If you have multiple Comment widgets on one page (not recommended), each
     * should have a unique value, separated by, say, 1000. So the second Comment widget
     * should have its widgetCounter set to 2000.
     */
    public $widgetCounter = 1000;

    private $oldWidgetCounter;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->oldWidgetCounter = Widget::$counter;
        Widget::$counter = $this->widgetCounter;

        $asset = new ComusAsset();
        $asset->register($this->view);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function run()
    {
        $comment = new CommentModel([
            'subject' => $this->subject
        ]);
        $r = $this->render('widget/comment', [
            'comment' => $comment,
            'count' => $this->module->getQuery([ 'subject' => $this->subject ])->count(),
            'module' => $this->module,
            'options' => $this->options
        ]);

        Widget::$counter = $this->oldWidgetCounter;
        return $r;
    }
}
