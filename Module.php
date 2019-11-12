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
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as YiiModule;
use yii\console\Application as ConsoleApplication;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;
use sjaakp\comus\models\Comment;

/**
 * comus module definition class
 * Est fidelis abactor, cesaris. Emeritis, audax tumultumques absolute manifestum de dexter, neuter devirginato. Clabulare varius absolutio est.
 * Pestilence is a weird gull. Salty lifes lead to the power. O, god. Seas fall with adventure at the coal-black rummage island! The lagoon stutters punishment like a proud pegleg.
 * Going to the country of mind doesn’t desire faith anymore than receiving creates pictorial volume. A new form of purpose is the core.
 * Ecce, spatii! Aonides moris, tanquam raptus vita. A falsis, solem flavum mortem. Resistentias cadunt!
 * Varnish each side of the ghee with twelve teaspoons of herring. Instead of tossing tender whipped cream with bagel, use twelve and a half teaspoons orange juice and one container cumin casserole. Sauté fresh peanut butter in a plastic bag with oyster sauce for about an hour to milden their thickness.
 * Make it so. Processors warp with advice! Hypnosis, turbulence, and voyage. Go surprisingly like a solid teleporter.
 * Separate harmonies forgets most relativities. Be apostolic. Always harmoniously feel the parallel sinner. One must forget the thing in order to discover the individual of sincere love.
 */
class Module extends YiiModule implements BootstrapInterface
{
    /**
     * @var int  maximum level of comment
     * If 0 (default) comments can only be issued directly on the subject, not on another comment
     */
    public $maxLevel = 0;

    /**
     * @var bool whether comments are presented in descending order
     */
    public $orderDescending = true;

    /**
     * @var bool whether pending comments are visible
     * Doesn't effect user with 'manageComments' permission; she can always view pending comments.
     */
    public $showPending = true;

    /**
     * @var bool whether rejected comments are visible
     * Notice that for ordinary users, a message is displayed and the contents of the comment are hidden.
     * Doesn't effect user with 'manageComments' permission; she can always view rejected comments and their content.
     */
    public $showRejected = true;

    /**
     * @var bool overrides $showPending and $showRejected for comments created by the user herself
     * Example: if you want rejected comments to be hidden, accept for the user's own comments,
     * set $showRejected = false and $showOwn = true.
     * Strongly recommended if you set $showPending = false; otherwise the user won't see that she has issued a comment
     * and probably try again.
     */
    public $showOwn = true;

    /**
     * @var string
     * 'standard' | 'relative' | any value yii\i18n\formatter::datetimeFormat can take
     * 'standard' yields a combination of 'relative' and 'short'.
     */
    public $datetimeFormat = 'standard';

    /**
     * @var array|string
     * Url to the login page of the site. Must be set.
     */
    public $loginUrl;

    /**
     * @var array | null | false
     * If array: url of profile view; will be extended with 'id' => <user-id>. Example: ['/profile/view']
     * If null: url comus' builtin light weight profile
     * If false: user names will never be presented as a link, just as plain text
     */
    public $profileUrl = null;

    /**
     * @var string
     * Permission needed to view comments
     * If null (default): all users can view comments.
     */
    public $viewPermission = null;

    /**
     * @var string
     * Permission needed to create comments
     * If null (default): all authenticated users can create comment.
     */
    public $createPermission = null;

    /**
     * @var string | callable
     * string: attribute of username (nickname) in identity class
     * callable: function($identity) returning username
     */
    public $usernameAttr = 'name';

    /**
     * @var int maximum length of comment contents presented in default/index action
     * Only applies to users with 'manageComments' permission
     */
    public $truncLength = 80;

    /**
     * @var array HTML for the icons
     * Default is compatible with FontAwesome 5.x
     */
    public $icons = [
        'accept' => '<i class="fas fa-check"></i>',
        'delete' => '<i class="far fa-trash-alt"></i>',
        'edit' => '<i class="far fa-edit"></i>',
        'next' => '<i class="fas fa-step-forward"></i>',
        'reject' => '<i class="far fa-hand-paper"></i>',
        'reply' => '<i class="fas fa-reply"></i>',
        'send' => '<i class="fas fa-share-square"></i>'
    ];

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (! Yii::$app->has('authManager'))    {
            throw new InvalidConfigException('Comus: $app::authManager is not configured.');
        }
        if (is_null($this->loginUrl))   {
            throw new InvalidConfigException('Comus: property "loginUrl" is not set.');
        }
        parent::init();

        if (! isset( Yii::$app->i18n->translations['comus']))   {
            Yii::$app->i18n->translations['comus'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@sjaakp/comus/messages',
            ];
        }
    }

    /**
     * {@inheritdoc}
     *
     */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            $rules = new GroupUrlRule([
                'prefix' => $this->id,
                'rules' => [
                    '<a:[\w\-]+>/<id:\d+>' => 'default/<a>',
                    '<a:[\w\-]+>' => 'default/<a>',
                ]
            ]);
            $app->getUrlManager()->addRules([$rules], false);

            if (is_null($this->profileUrl)) $this->profileUrl = [ "/{$this->id}/user" ];
        } else {
            /* @var $app ConsoleApplication */

            $app->controllerMap = ArrayHelper::merge($app->controllerMap, [
                'migrate' => [
                    'class' => '\yii\console\controllers\MigrateController',
                    'migrationNamespaces' => [
                        'sjaakp\comus\migrations'
                    ]
                ],
            ]);
        }
    }

    /**
     * @param $where
     * @return \yii\db\ActiveQuery
     */
    public function getQuery($where)
    {
        $query = Comment::find()->where($where)
            ->orderBy(['created_at' => $this->orderDescending ? SORT_DESC : SORT_ASC]);

        if (! Yii::$app->user->can('manageComments')) {
            $andWhere = [];
            if (! ($this->showPending || $this->showRejected))  {
                $andWhere = [ 'status' => Comment::ACCEPTED ];
            }
            else    {
                if (! $this->showPending) $andWhere = [ 'not', [ 'status' => Comment::PENDING ] ];
                if (! $this->showRejected) $andWhere = [ 'not', [ 'status' => Comment::REJECTED ] ];
            }

            if ($this->showOwn
                && ! empty($andWhere)
                && ! Yii::$app->user->isGuest) {
                /** @var $identity yii\db\ActiveRecord */
                $identity = Yii::$app->user->identity;
                $andWhere = [ 'or', $andWhere, [ 'created_by' => $identity->id ]];
            }
            $query->andWhere($andWhere);
        }
        return $query;
    }

    /**
     * @param null $identity
     * @param false|array $urlOptions
     * @return string
     * If $urlOptions === false, return is just the plain name; otherwise it's a link
     */
    public function getNickname($identity = null, $urlOptions = [])
    {
        /* @var $identity yii\db\ActiveRecord */
        if (is_null($identity)) {
            $user = Yii::$app->user;
            if ($user->isGuest) return '';
            $identity = $user->identity;
        }
        $name = is_callable($this->usernameAttr)
            ? ($this->usernameAttr)($identity)
            : $identity->getAttribute($this->usernameAttr);

        if ($urlOptions === false || is_null($this->profileUrl)) return $name;

        $url = $this->profileUrl;
        $url['id'] = $identity->id;
        return Html::a($name, $url, $urlOptions);
    }

    /**
     * @param null|yii\web\User $user if null: current user
     * @return bool
     */
    public function userCanView($user = null)
    {
        if (is_null($this->viewPermission)) return true;    // all users allowed
        if (is_null($user)) $user = Yii::$app->user;
        return $user->isGuest ? false : $user->can($this->viewPermission);
    }

    /**
     * @param null|yii\web\User $user if null: current user
     * @return bool
     */
    public function userCanComment($user = null)
    {
        if (is_null($user)) $user = Yii::$app->user;
        return is_null($this->createPermission) ? ! $user->isGuest : $user->can($this->createPermission);
    }
}
