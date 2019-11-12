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

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Inflector;

class ComusBase extends Widget
{
    /**
     * @var yii\db\BaseActiveRecord | null
     */
    public $model;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $moduleId = 'comment';

    /**
     * @var \sjaakp\comus\Module
     */
    protected $module;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (! empty($this->model))  {
            $controllerId = Inflector::camel2id($this->model->formName());
            $id = $this->model->primaryKey;
            if (is_array($id))  {
                throw new InvalidConfigException('Model has composite primary key; this is not allowed.');
            }
            $this->subject = "$controllerId/$id";
        }

        if (empty($this->subject)) {
            throw new InvalidConfigException('Either "model" or "subject" property must be set.');
        }

        $this->module = Yii::$app->getModule($this->moduleId);
    }
}
