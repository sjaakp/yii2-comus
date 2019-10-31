<?php

namespace sjaakp\comus;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as YiiModule;
use yii\console\Application as ConsoleApplication;
use yii\helpers\ArrayHelper;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;

/**
 * comus module definition class
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
     */
    public $loginPrompt = '<a href="/pluto/login">Login</a> to comment';

    /**
     * @var string
     * Permission needed to create comments
     * If null (default): all authenticated users can create comment.
     */
    public $permission = null;

    /**
     * @var string | callable
     * string: attribute of username (nickname) in identity class
     * callable: function($identity) returning username
     */
    public $usernameAttr = 'name';

    /**
     * {@inheritdoc}
     */
    public $basePath = '@sjaakp\comus';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'sjaakp\comus\controllers';

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (! Yii::$app->has('authManager'))    {
            throw new InvalidConfigException('$app::authManager is not configured.');
        }
        parent::init();

        if (! isset( Yii::$app->i18n->translations['comus']))   {
            Yii::$app->i18n->translations['commus'] = [
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
     * @return string
     */
    public function getNickname($identity = null)
    {
        /* @var $identity yii\db\ActiveRecord */
        if (is_null($identity)) {
            $user = Yii::$app->user;
            if ($user->isGuest) return '';
            $identity = $user->identity;
        }
        return is_callable($this->usernameAttr)
            ? ($this->usernameAttr)($identity)
            : $identity->getAttribute($this->usernameAttr);
    }

    /**
     * @param null|yii\web\User $user if null: current user
     * @return bool
     */
    public function userCanComment($user = null)
    {
        if (is_null($user)) $user = Yii::$app->user;
        return is_null($this->permission) ? ! $user->isGuest : $user->can($this->permission);
    }

    /**
     * @return string the namespace of the Bootstrap extension ('yii\bootstrap' or 'yii\bootstrap4')
     * @throws InvalidConfigException
     */
    public function bootstrapNamespace()
    {
        foreach ([ '4', '3', ''] as $v)  {
            $ns = 'yii/bootstrap' . $v;
            if (strrpos(Yii::getAlias( '@' . $ns, false),'/src') !== false) return str_replace('/', '\\', $ns);
        }
        throw new InvalidConfigException( 'No Bootstrap extension found');
    }

}
