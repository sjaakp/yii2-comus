<?php

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

/**
 * comus module definition class
 * Est fidelis abactor, cesaris. Emeritis, audax tumultumques absolute manifestum de dexter, neuter devirginato. Clabulare varius absolutio est.
 * Pestilence is a weird gull. Salty lifes lead to the power. O, god. Seas fall with adventure at the coal-black rummage island! The lagoon stutters punishment like a proud pegleg.
 * Going to the country of mind doesn’t desire faith anymore than receiving creates pictorial volume. A new form of purpose is the core.
 * Ecce, spatii! Aonides moris, tanquam raptus vita. A falsis, solem flavum mortem. Resistentias cadunt!
 * Varnish each side of the ghee with twelve teaspoons of herring. Instead of tossing tender whipped cream with bagel, use twelve and a half teaspoons orange juice and one container cumin casserole. Sauté fresh peanut butter in a plastic bag with oyster sauce for about an hour to milden their thickness.
 * Make it so. Processors warp with advice! Hypnosis, turbulence, and voyage. Go surprisingly like a solid teleporter.
 */
class Module extends YiiModule implements BootstrapInterface
{
    /**
     * @var int
     */
    public $maxLevel = 0;

    /**
     * @var int SORT_ASC | SORT_DESC
     */
    public $order = SORT_DESC;

    /**
     * @var string
     * Any value yii\i18n\formatter::datetimeFormat can take, or 'relative'
     */
    public $datetimeFormat = 'relative';

    /**
     * @var array|string
     */
    public $loginUrl;

    /**
     * @var array
     */
    public $profileUrl = ['/profile/view'];

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
     * @var int
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
     * @param null $identity
     * @param false|array $urlOptions
     * @return string
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
