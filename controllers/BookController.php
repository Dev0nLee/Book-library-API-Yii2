<?php

namespace app\controllers;

use app\models\Book;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\httpclient\Client;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

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
                'only' => ['index', 'read-file', 'view-user-library', 'search-page', 'search-ajax', 'save-book-ajax'],
                'rules' => [
                    [
                        'actions' => ['index', 'read-file', 'view-user-library', 'search-page', 'search-ajax', 'save-book-ajax'],
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

    /**
     * Search books using Google Books API and MIF search
     * @param string $query Search query for book title
     * @return array List of found books
     * @throws BadRequestHttpException
     */
    public function actionSearch($query)
    {
        if (empty($query)) {
            throw new BadRequestHttpException('Search query cannot be empty');
        }

        $client = new Client();
        $books = [];

        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://www.googleapis.com/books/v1/volumes')
                ->setData(['q' => $query])
                ->send();

            if ($response->isOk && isset($response->data['items'])) {
                $googleBooks = $response->data['items'];
                foreach ($googleBooks as $book) {
                    $volumeInfo = $book['volumeInfo'] ?? [];
                    $books[] = [
                        'id' => $book['id'],
                        'title' => $volumeInfo['title'] ?? 'Без названия',
                        'authors' => $volumeInfo['authors'] ?? ['Неизвестный автор'],
                        'description' => $volumeInfo['description'] ?? 'Описание отсутствует',
                        'source' => 'google',
                        'url' => $volumeInfo['infoLink'] ?? '',
                        'image' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                        'publishedDate' => $volumeInfo['publishedDate'] ?? '',
                        'pageCount' => $volumeInfo['pageCount'] ?? 0
                    ];
                }
            }
        } catch (\Exception $e) {
            \Yii::error('Google Books API error: ' . $e->getMessage());
        }

        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://www.mann-ivanov-ferber.ru/book/search.ajax')
                ->setData(['q' => $query])
                ->send();

            if ($response->isOk && !empty($response->data)) {
                $mifBooks = is_array($response->data) ? $response->data : [$response->data];
                foreach ($mifBooks as $book) {
                    if (is_array($book) && isset($book['title'])) {
                        $books[] = [
                            'id' => $book['id'] ?? uniqid('mif_'),
                            'title' => $book['title'] ?? 'Без названия',
                            'authors' => [$book['author'] ?? 'Неизвестный автор'],
                            'description' => $book['description'] ?? 'Описание отсутствует',
                            'source' => 'mif',
                            'url' => $book['url'] ?? '',
                            'image' => $book['image'] ?? '',
                            'publishedDate' => $book['published_date'] ?? '',
                            'pageCount' => $book['page_count'] ?? 0
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Yii::error('MIF API error: ' . $e->getMessage());
        }

        return $books;
    }

    /**
     * AJAX search books using Google Books API and MIF search
     * @return \yii\web\Response
     */
    public function actionSearchAjax()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $query = \Yii::$app->request->get('q');
        
        if (empty($query)) {
            return ['success' => false, 'message' => 'Поисковый запрос не может быть пустым'];
        }
        
        try {
            $books = $this->actionSearch($query);
            return [
                'success' => true,
                'books' => $books,
                'count' => count($books),
                'message' => 'Поиск выполнен успешно'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при поиске: ' . $e->getMessage()
            ];
        }
    }

    /**
     * AJAX save book to database
     * @return \yii\web\Response
     */
    public function actionSaveBookAjax()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $result = $this->actionSaveBook();
            return $result;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при сохранении: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Save a book to the database
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionSaveBook()
    {
        $id = \Yii::$app->request->post('id');
        $source = \Yii::$app->request->post('source');
        $userId = \Yii::$app->user->id;

        if (empty($id) || empty($source) || empty($userId)) {
            throw new BadRequestHttpException('Missing required parameters: id, source, or user_id');
        }

        $client = new Client();
        $bookData = null;

        if ($source === 'google') {
            try {
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl("https://www.googleapis.com/books/v1/volumes/{$id}")
                    ->send();

                if (!$response->isOk) {
                    throw new NotFoundHttpException('Книга не найдена в Google Books');
                }

                $bookData = $response->data;
                $volumeInfo = $bookData['volumeInfo'] ?? [];
                $title = $volumeInfo['title'] ?? 'Без названия';
                $description = $volumeInfo['description'] ?? $volumeInfo['infoLink'] ?? '';

                if (empty($description)) {
                    $description = $volumeInfo['infoLink'] ?? '';
                }
            } catch (\Exception $e) {
                \Yii::error('Google Books API error in saveBook: ' . $e->getMessage());
                throw new BadRequestHttpException('Ошибка при получении данных книги из Google Books');
            }
        } elseif ($source === 'mif') {
            try {
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl("https://www.mann-ivanov-ferber.ru/book/search.ajax")
                    ->setData(['q' => $id])
                    ->send();

                if (!$response->isOk || empty($response->data)) {
                    throw new NotFoundHttpException('Книга не найдена в MIF');
                }

                $mifBooks = is_array($response->data) ? $response->data : [$response->data];
                $bookData = $mifBooks[0] ?? null;
                
                if (!$bookData || !isset($bookData['title'])) {
                    throw new NotFoundHttpException('Данные книги не найдены в MIF');
                }
                
                $title = $bookData['title'] ?? 'Без названия';
                $description = $bookData['description'] ?? $bookData['url'] ?? '';

                if (empty($description)) {
                    $description = $bookData['url'] ?? '';
                }
            } catch (\Exception $e) {
                \Yii::error('MIF API error in saveBook: ' . $e->getMessage());
                throw new BadRequestHttpException('Ошибка при получении данных книги из MIF');
            }
        } else {
            throw new BadRequestHttpException('Неверный источник книги');
        }

        $existingBook = Book::find()
            ->where(['title' => $title, 'user_id' => $userId])
            ->one();
            
        if ($existingBook) {
            return [
                'status' => 'error',
                'message' => 'Книга с таким названием уже существует в вашей библиотеке',
                'book_id' => $existingBook->id
            ];
        }
        
        $book = new Book();
        $book->title = $title;
        $book->text = $description; 
        $book->user_id = $userId;
        $book->status = 1;

        if ($book->save()) {
            return [
                'status' => 'success',
                'message' => 'Книга успешно сохранена в библиотеку',
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'text' => $book->text,
                    'user_id' => $book->user_id
                ]
            ];
        }

        throw new BadRequestHttpException('Не удалось сохранить книгу: ' . json_encode($book->errors));
    }

    /**
     * Display search page for books
     * @return string
     */
    public function actionSearchPage()
    {
        return $this->render('search');
    }
}
