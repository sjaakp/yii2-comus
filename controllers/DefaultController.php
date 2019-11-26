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

namespace sjaakp\comus\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use sjaakp\comus\Module;
use sjaakp\comus\models\Comment;

/**
 * Class DefaultController
 * @package sjaakp\comus
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'previous', 'next'],
                'rules' => [
                    [
                        'allow' => true,
                        'permissions' => ['manageComments']
                    ],
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => [ 'GET' ],
                    'previous' => [ 'GET' ],
                    'next' => [ 'GET' ],
                    'user' => [ 'GET' ],
                    '*' => [ 'POST' ],
                ],
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex($s = Comment::PENDING)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Comment::find()
                ->where([ 'status' => $s ])
                ->orderBy([ 'created_at' => $this->module->orderDescending ? SORT_DESC : SORT_ASC ]),
            'sort' => false
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'category' => $s,
            'module' => $this->module
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionUser($id)
    {
        /* @var $iClass yii\db\ActiveRecord */
        $iClass = Yii::$app->user->identityClass;
        $identity = $iClass::findOne($id);

        return $this->render('user', [
            'module' => $this->module,
            'identity' => $identity,
        ]);
    }

    /**
     * @return \yii\web\Response
     * Redirect to next pending comment if available, to index otherwise
     */
    public function actionNext($after)
    {
        $desc = $this->module->orderDescending;
        return $this->step($desc ? '<' : '>', $desc ? SORT_DESC : SORT_ASC, $after);
    }

    /**
     * @return \yii\web\Response
     * Redirect to previous pending comment if available, to index otherwise
     */
    public function actionPrevious($before)
    {
        $desc = $this->module->orderDescending;
        return $this->step($desc ? '>' : '<', $desc ? SORT_ASC : SORT_DESC, $before);
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        /* @var $module Module */
        $module = $this->module;
        return $module->userCanComment() ? $this->refreshWidget(new Comment()) : '';
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        return Yii::$app->user->can('manageComments', $model) ? $this->refreshWidget($model) : '';
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        return Yii::$app->user->can('updateComment', $model) ? $this->refreshWidget($model) : '';
    }

    /**
     * Deletes an existing Comment model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (! Yii::$app->user->can('deleteComment', $model)) return '';

        $subject = $model->subject;
        $model->delete();

        return $this->renderAjax('refresh', [
            'subject' => $subject,
            'moduleId' => $this->module->id
        ]);
    }

    /**
     * @return \yii\web\Response
     * Redirect to previous/next pending comment if available, to index otherwise
     */
    protected function step($operator, $sort, $dt)
    {
        $comment = Comment::find()
            ->where([ 'status' => Comment::PENDING ])
            ->andWhere([ $operator, 'created_at', $dt ])
            ->orderBy([ 'created_at' => $sort ])
            ->one();

        return $this->redirect($comment ? $comment->href : 'index');
    }

    /**
     * @param $model Comment
     * @return string
     */
    protected function refreshWidget($model)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderAjax('refresh', [
                'subject' => $model->subject,
                'moduleId' => $this->module->id
            ]);
        }
        return '';
    }

    /**
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Comment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('comus', 'The requested Comment does not exist.'));
    }
}
