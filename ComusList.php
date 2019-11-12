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

use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

class ComusList extends ListView
{
    /**
     * @var string
     */
    public $subject;

    /**
     * @var int
     */
    public $parent = null;

    /**
     * @var int
     */
    public $level = 0;

    /**
     * @var \sjaakp\comus\Module
     */
    public $module;

    public $itemView = '_item';
    public $summary = '';
    public $options = [
        'tag' => 'ol',
        'class' => 'comus-list list-unstyled'
    ];

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->dataProvider = new ActiveDataProvider([
            'query' => $this->module->getQuery([
                'subject' => $this->subject,
                'parent' => $this->parent
            ]),
            'pagination' => false,
            'sort' => false
        ]);
        $this->itemOptions = function($model, $key, $index, $widget)   {
            return [
                'tag' => 'li',
                'id' => 'cmt-' . $model->id
            ];
        };
        $this->viewParams = [
            'module' => $this->module,
            'level' => $this->level
        ];
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRun()
    {
        if (!parent::beforeRun()) {
            return false;
        }
        return $this->dataProvider->getTotalCount() > 0; // do not run if no data
    }
}
