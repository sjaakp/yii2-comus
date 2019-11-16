<?php

namespace sjaakp\comus\rbac;

use yii\rbac\Rule;

/**
 * Class CreatorRule
 * Checks whether a model is created by a user
 * @package sjaakp\comus
 */
class CreatorRule extends Rule
{
    public $name = 'isCreator';

    /**
     * @param string|int $user the user id.
     * @param \yii\rbac\Item $item the Role or Permission that this Rule is associated with
     * @param $params object|array, one of:
     *     -    yii\base\Model
     *     -    [ 'model' => <yii\base\Model>, 'attribute' => <string> ]
     *      'attribute' is optional; default is 'created_by'
     * @return bool whether the Rule permits the Role or Permission.
     */
    public function execute($user, $item, $params)
    {
        if (! is_array($params)) $params = ['model' => $params];
        $model = $params['model'];
        $attribute = $params['attribute'] ?? 'created_by';
        return $user == $model->$attribute;
    }
}
