<?php

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
 * Default controller for the Comus module
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
                'only' => ['index', 'pending'],
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
                    'index' => [ 'GET', 'POST' ],
                    'pending' => [ 'GET', 'POST' ],
                    '*' => [ 'POST' ],
                ],
            ],
        ];
    }

    /**
     * Lists all Comment models.
     * @return mixed
     */
    public function actionIndex($s = Comment::PENDING)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Comment::find()
                ->where([ 'status' => $s ])
                ->orderBy([ 'created_at' => $this->module->order ]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'category' => $s,
            'module' => $this->module
        ]);
    }

    /**
     * @return \yii\web\Response
     * Redirect to first pending comment if available, to index otherwise
     */
    public function actionPending()
    {
        $comment = Comment::find()
            ->where([ 'status' => Comment::PENDING ])
            ->orderBy([ 'created_at' => $this->module->order ])
            ->one();

        return $this->redirect($comment ? $comment->url : [ 'index' ]);
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
