<?php


namespace sjaakp\comus;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use sjaakp\comus\models\Comment as CommentModel;
use yii\helpers\Inflector;

class Comment extends Widget
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

        if (! empty($this->model))  {
            $controllerId = Inflector::camel2id($this->model->formName());
            $id = $this->model->primaryKey;
            if (is_array($id))  {
                throw new InvalidConfigException(Yii::t('comus', 'Model has composite primary key; this is not allowed.'));
            }
            $this->subject = "$controllerId/$id";
        }

        if (empty($this->subject)) {
            throw new InvalidConfigException(Yii::t('comus', 'Either "model" or "subject" property must be set.'));
        }
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
        $module = Yii::$app->getModule($this->moduleId);
        $count = CommentModel::find()->where([ 'subject' => $this->subject ])->count();

        $r = $this->render('widget/comment', [
            'comment' => $comment,
            'count' => $count,
            'module' => $module,
            'options' => $this->options
        ]);

        Widget::$counter = $this->oldWidgetCounter;
        return $r;
    }

}