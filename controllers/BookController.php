<?php

namespace app\controllers;

use app\models\Book;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'read-file', 'view-user-library'],
                'rules' => [
                    [
                        'actions' => ['index', 'read-file', 'view-user-library'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            ]
        );
    }

    /**
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Book::find()->where(['user_id' => \Yii::$app->user->id])->andFilterWhere(['status' => 1]),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Book models of another user (if access granted).
     *
     * @param int $user_id ID of the user whose library to view
     * @return string
     * @throws \yii\web\ForbiddenHttpException if access not granted
     */
    public function actionViewUserLibrary($user_id)
    {
        $hasAccess = \app\models\UserAccess::find()
            ->where(['owner_id' => $user_id, 'viewer_id' => \Yii::$app->user->id])
            ->exists();

        if (!$hasAccess) {
            throw new \yii\web\ForbiddenHttpException('У вас нет доступа к библиотеке этого пользователя.');
        }

        $user = \app\models\User::findOne($user_id);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('Пользователь не найден.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Book::find()->where(['user_id' => $user_id])->andFilterWhere(['status' => 1]),
        ]);

        return $this->render('user_library', [
            'dataProvider' => $dataProvider,
            'user' => $user,
        ]);
    }

    /**
     * Lists all deleted Book models.
     *
     * @return string
     */
    public function actionDeletedBook()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Book::find()->where(['user_id' => \Yii::$app->user->id])->andFilterWhere(['status' => 0]),

        ]);

        return $this->render('del_books', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Book();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->status = 1;
            $model->user_id = \Yii::$app->user->id;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->user_id !== \Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Вы не можете изменять эту книгу.');
        }

        if ($this->request->isPost) {
            $model->load($this->request->post());
            
            $model->textFile = UploadedFile::getInstance($model, 'textFile');
            
            if ($model->textFile) {
                $textFromFile = $model->readTextFromFile();
                if ($textFromFile !== false) {
                    $model->text = $textFromFile;
                }
            }
            
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Update status on 0 an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->user_id !== \Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Вы не можете удалить эту книгу.');
        }

        $model->status = 0;
        $model->save();

        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionFullDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->user_id !== \Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Вы не можете удалить эту книгу.');
        }
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Update status on 1 an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id);

        if ($model->user_id !== \Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('Вы не можете восстановить эту книгу.');
        }

        $model->status = 1;
        $model->save();

        return $this->redirect(['index']);
    }

    /**
     * AJAX read text from file
     * @return \yii\web\Response
     */
    public function actionReadFile()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $model = new Book();
        $model->textFile = UploadedFile::getInstance($model, 'textFile');
        
        if (!$model->textFile) {
            return ['success' => false, 'message' => 'Файл не загружен'];
        }
        
        $textFromFile = $model->readTextFromFile();
        if ($textFromFile === false) {
            return ['success' => false, 'message' => 'Не удалось прочитать файл'];
        }
        
        return [
            'success' => true, 
            'text' => $textFromFile,
            'message' => 'Текст успешно прочитан из файла'
        ];
    }

    /**
     * Finds the Book model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Book the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
