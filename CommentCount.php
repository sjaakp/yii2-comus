<?php


namespace sjaakp\comus;

use yii\base\InvalidConfigException;
use sjaakp\comus\models\Comment;
use yii\base\Widget;

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
     * @var bool
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
        $count = Comment::find()->where([ 'subject' => $this->subject ])->count();
        if ($count > 0 || $this->showZero)  {
            return $this->template ? str_replace([ '{href}', '{count}' ], [ $this->subject . '#cmts', $count ], $this->template) : $count;
        }
        return '';
    }
}
