<?php

namespace sjaakp\comus;

use yii\base\widget;
use yii\data\ActiveDataProvider;
use sjaakp\comus\models\Comment as CommentModel;

class CommentList extends Widget
{
    /**
     * @var string
     */
    public $subject;

    /**
     * @var \sjaakp\comus\Module
     */
    public $module;

    /**
     * @var int
     */
    public $parent = null;

    /**
     * @var int
     */
    public $level = 0;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $provider = new ActiveDataProvider([
            'query' => CommentModel::find()->where([
                'subject' => $this->subject,
                'parent' => $this->parent
            ])->orderBy([ 'created_at' => $this->module->order ]),
            'pagination' => false
        ]);
        $baseId = str_replace('/', '-', $this->subject);
        if ($this->parent) $baseId .= '-' . $this->parent;

        return $this->render('widget/list', [
            'provider' => $provider,
            'module' => $this->module,
            'baseId' => $baseId,
            'level' => $this->level
        ]);
    }
}
