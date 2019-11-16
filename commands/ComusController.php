<?php
namespace sjaakp\comus\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use sjaakp\comus\rbac\CreatorRule;

/**
 * Class ComusController
 * @package sjaakp\comus
 */
class ComusController extends Controller
{
    /**
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function actionIndex()
    {
        $auth = Yii::$app->authManager;
        if (! $auth->getRule('isCreator'))  {
            $auth->add(new CreatorRule);
        }

        echo "Rules set\n";

        $permissions = [
            'createComment' => Yii::t('comus', 'Create comment'),
            'deleteComment' => Yii::t('comus', 'Delete comment'),
            'updateComment' => Yii::t('comus', 'Update comment'),
            'deleteOwnComment' => Yii::t('comus', 'Delete self-created comment'),
            'updateOwnComment' => Yii::t('comus', 'Update self-created comment'),
            'manageComments' => Yii::t('comus', 'Manage comments'),
        ];

        foreach($permissions as $key => $desc)  {
            $item = $auth->createPermission($key);
            $item->description = $desc;
            $permissions[$key] = $item;
            $auth->add($item);
        }

        $permissions['deleteOwnComment']->ruleName = 'isCreator';
        $auth->addChild($permissions['deleteOwnComment'], $permissions['deleteComment']);

        $permissions['updateOwnComment']->ruleName = 'isCreator';
        $auth->addChild($permissions['updateOwnComment'], $permissions['updateComment']);

        echo "Permissions set\n";

        $moderator = $auth->createRole('moderator');
        $moderator->description = Yii::t('comus', 'Can accept, reject, update or delete all comments');
        $auth->add($moderator);
        $auth->addChild($moderator, $permissions['deleteComment']);
        $auth->addChild($moderator, $permissions['updateComment']);
        $auth->addChild($moderator, $permissions['manageComments']);

        $admin = $auth->getRole('admin');
        if ($admin) {
            $auth->addChild($admin, $moderator);
        }

        echo "Roles set\nComus completed\n";

        return ExitCode::OK;
    }
}
