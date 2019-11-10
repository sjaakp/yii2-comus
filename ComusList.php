<?php

namespace sjaakp\comus;

use yii\data\ActiveDataProvider;
use yii\widgets\ListView;
use sjaakp\comus\models\Comment;

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
            'query' => Comment::find()->where([
                'subject' => $this->subject,
                'parent' => $this->parent
            ])->orderBy(['created_at' => $this->module->order]),
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
