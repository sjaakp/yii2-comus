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

namespace sjaakp\comus\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use sjaakp\novelty\NoveltyBehavior;
use yii\db\Expression;

/**
 * Class Comment
 * @package sjaakp\comus
 *
 * @property int $id
 * @property string $subject
 * @property int $parent
 * @property int $status
 * @property string $body
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Comment extends ActiveRecord
{
    const PENDING = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;

    const STATUSES = [
        self::PENDING => 'pending',
        self::ACCEPTED => 'accepted',
        self::REJECTED => 'rejected'
    ];

    /**
     * @var array
     */
    public $purifierConfig = [
        'HTML.Allowed' => 'p,a[href]',
        'AutoFormat.AutoParagraph' => true
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => NoveltyBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            BlameableBehavior::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $children = self::find()->where([ 'parent' => $this->id ])->all();
        foreach($children as $child)  {
            try {
                $child->delete();
            } catch (\Throwable $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getClasses()
    {
        $r = [ 'comus-' . self::STATUSES[$this->status] ];
        $novelty = $this->novelty;     // null|'new'|'updated'
        if (! empty($novelty)) $r[] = 'comus-' . $novelty;
        if (Yii::$app->user->id == $this->created_by) $r[] = 'comus-own';
        if (Yii::$app->authManager->checkAccess($this->created_by, 'manageComments')) $r[] = 'comus-moderator';
        return implode(' ', $r);
    }

    /**
     * @return string
     */
    public function getSanitizedBody()
    {
        $body = Html::tag('div', $this->body, [ 'class' => 'comus-body' ]);
        if ($this->status == self::REJECTED)    {
            $r = Html::tag('div', Yii::t('comus', 'This comment has been rejected.'), [ 'class' => 'comus-message' ]);
            if (Yii::$app->user->can('manageComments')) $r .= $body;
            return $r;
        }
        return $body;
    }

    /**
     * @param $format
     * @return string  'standard' | 'relative' | any value yii\i18n\formatter::datetimeFormat can take
     * @throws \yii\base\InvalidConfigException
     */
    public function getFormattedTime($format)
    {
        $formatter = Yii::$app->formatter;
        if ($format == 'standard')  {
            return $formatter->asRelativeTime($this->created_at) . Html::tag('span', $formatter->asDatetime($this->created_at, 'short'), [ 'class' => 'comus-short' ]);
        }
        return $format == 'relative' ? $formatter->asRelativeTime($this->created_at) : $formatter->asDatetime($this->created_at, $format);
    }

    /**
     * @return array
     */
    public function getHref()
    {
        return [ $this->subject, '#' => "cmt-{$this->id}" ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'updated_by']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // @link https://stackoverflow.com/questions/30124559/yii2-how-to-validate-xss-cross-site-scripting-in-form-model-input/30124560
           [['body'], 'filter', 'filter' => function($in) { return HtmlPurifier::process($in, $this->purifierConfig); }],
//            [['body'], 'required', 'message' => Yii::t('comus', 'Comment cannot be blank')],  // replaced by HTML required
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'subject', 'parent', 'status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('comus', 'ID'),
            'subject' => Yii::t('comus', 'Subject'),
            'body' => Yii::t('comus', 'Body'),
            'created_by' => Yii::t('comus', 'Created By'),
            'updated_by' => Yii::t('comus', 'Updated By'),
            'created_at' => Yii::t('comus', 'Created At'),
            'updated_at' => Yii::t('comus', 'Updated At'),
            'createdBy.name' => Yii::t('comus', 'Created By'),
            'updatedBy.name' => Yii::t('comus', 'Updated By'),
        ];
    }
}
