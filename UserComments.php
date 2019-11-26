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
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use sjaakp\comus\models\Comment;

/**
 * Class UserComments
 * @package sjaakp\comus
 */
class UserComments extends GridView
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     * 'standard' | 'relative' | any value yii\i18n\formatter::datetimeFormat can take
     * 'standard' yields a combination of 'relative' and 'short'.
     */
    public $datetimeFormat = 'short';

    public $options = [ 'class' => 'comus-user-comments' ];
    public $tableOptions = ['class' => 'table table-sm table-bordered'];
    public $emptyText = false;
    public $showOnEmpty = false;

    /**
     * @var string
     */
    public $moduleId = 'comment';

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        /** @var $module \sjaakp\comus\Module */
        $module = Yii::$app->getModule($this->moduleId);
        $query = $module->getQuery([ 'created_by' => $this->userId ]);

        $this->dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);

        $dtFormat = $this->datetimeFormat ?? $module->datetimeFormat;
        $this->columns = [
            [
                'attribute' => 'created_at',
                'content' => function ($model, $key, $index, $widget) use ($dtFormat) {
                    /* @var $model Comment */
                    return $model->getFormattedTime($dtFormat);
                },
                'contentOptions' => [ 'class' => 'small' ]
            ],
            [
                'attribute' => 'body',
                'content' => function ($model, $key, $index, $widget) use ($module)  {
                    return Html::a(StringHelper::truncate($model->sanitizedBody, $module->truncLength), $model->href);
                },
                'format' => 'html'
            ],
        ];
        $this->rowOptions = function ($model, $key, $index, $widget)  {
            return [ 'class' => $model->classes ];
        };
        $this->summary = Html::tag('div', Yii::t('comus', 'Comments ({n,number})', [
            'n' => $this->dataProvider->totalCount
        ]), [ 'class' => 'comus-user-summary' ]);
        parent::init();

        $asset = new ComusAsset();
        $asset->register($this->view);
    }
}
