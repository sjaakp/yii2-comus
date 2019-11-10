<?php

namespace sjaakp\comus;

use Yii;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use sjaakp\comus\models\Comment;

class UserComments extends GridView
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var string   Any value yii\i18n\formatter::datetimeFormat can take, or 'relative'
     */
    public $datetimeFormat = 'short';

    /**
     * @var int
     */
    public $truncLength = 60;

    public $options = [ 'class' => 'comus-user-comments' ];
    public $tableOptions = ['class' => 'table table-sm table-bordered'];
    public $emptyText = false;
    public $showOnEmpty = false;

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->dataProvider = new ActiveDataProvider([
            'query' => Comment::find()->where([ 'created_by' => $this->userId ]),
            'sort' => false
        ]);
        $this->columns = [
            [
                'attribute' => 'created_at',
                'value' => function ($model, $key, $index, $widget)  {
                    return $model->getFormattedTime($this->datetimeFormat);
                },
            ],
            [
                'attribute' => 'body',
                'value' => function ($model, $key, $index, $widget)  {
                    return Html::a(StringHelper::truncate($model->sanitizedBody, $this->truncLength), $model->href);
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
