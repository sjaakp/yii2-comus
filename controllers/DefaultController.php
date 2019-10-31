<?php

namespace sjaakp\comus\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use sjaakp\comus\models\Comment;

/**
 * Default controller for the `comment` module
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
/*            'access' => [
                'class' => AccessControl::class,
                'only' => ['view', 'create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@']       // allow all authenticated users
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'permissions' => ['createItem']
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'permissions' => ['deleteItem']
                    ],
                ]
            ],*/
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
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
                ->orderBy([ 'created_at' => SORT_DESC ]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'category' => $s,
            'module' => $this->module
        ]);
    }

    /**
     * Displays a single Comment model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
/*    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }*/

    /**
     * @param $model Comment
     * @return string
     */
    protected function cuHelper($model)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderAjax('cu', [
                'subject' => $model->subject,
                'moduleId' => $this->module->id
            ]);
        }
        return '';
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        return $this->cuHelper(new Comment());
/*
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderAjax('create', [
                'subject' => $model->subject,
                'moduleId' => $this->module->id
            ]);
        }
        return '';*/
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        return $this->cuHelper($this->findModel($id));
/*        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->renderAjax('create', [
                'subject' => $model->subject,
                'moduleId' => $this->module->id
            ]);
        }
        return '';*/
    }

    /**
     * Updates an existing LuckyNumber model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException if the model cannot be found
     */
/*    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (! Yii::$app->user->can('updateItem', $model))   {
            throw new ForbiddenHttpException(Yii::t('comus', 'Sorry, you\'re not allowed to update this Comment'));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }*/

    /**
     * Deletes an existing LuckyNumber model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $subject = $model->subject;
        $model->delete();

        return $this->renderAjax('cu', [
            'subject' => $subject,
            'moduleId' => $this->module->id
        ]);
//        return $this->redirect(['/' . $subject]);
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
