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

/**
 * Class CommentCount
 * @package sjaakp\comus
 */
class CommentCount extends ComusBase
{
    /**
     * @var null|string
     * If null: outputs count as plain text
     * Otherwise: outputs template, where:
     *  '{count}'   is replaced by count
     *  '{href}'    is the url to the comment section of the model view
     */
    public $template = '<a class="comus-count" href="{href}">{count}</a>';

    /**
     * @var bool  whether to show the counter if there are no comments
     */
    public $showZero = false;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $asset = new CountAsset();
        $asset->register($this->view);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function run()
    {
        $count = $this->module->getQuery([ 'subject' => $this->subject ])->count();

        if ($count > 0 || $this->showZero)  {
            return $this->template ? str_replace([ '{href}', '{count}' ], [ $this->subject . '#cmts', $count ], $this->template) : $count;
        }
        return '';
    }
}
