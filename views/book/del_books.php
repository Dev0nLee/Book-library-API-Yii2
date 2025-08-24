<?php

use app\models\Book;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Книги';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',

            [
                'class' => ActionColumn::className(),
                'template' => '{view} {restore} {full-delete}',
                'urlCreator' => function ($action, Book $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 },
                 'buttons' => [
                    'restore' => function ($url, $model, $key) {
                        return Html::a(
                            'REF',
                            $url,
                            [
                                'title' => 'Восстановить',
                                'data-confirm' => 'Вы уверены, что хотите восстановить эту книгу?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                    'full-delete' => function ($url, $model, $key) {
                        return Html::a(
                            'DEL',
                            $url,
                            [
                                'title' => 'Удалить навсегда',
                                'data-confirm' => 'Вы уверены, что хотите навсегда удалить эту книгу?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>


</div>
