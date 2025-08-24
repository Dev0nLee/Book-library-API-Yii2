<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'name',
            [
                'class' => ActionColumn::className(),
                'template' => '{get-access} {view-library}',
                'urlCreator' => function ($action, User $model, $key, $index, $column) {
                    if ($action === 'get-access') {
                        return Url::toRoute(['get-access', 'user_id' => $model->id]);
                    }
                    if ($action === 'view-library') {
                        return Url::toRoute(['/book/view-user-library', 'user_id' => $model->id]);
                    }
                    return '#';
                },
                'buttons' => [
                    'get-access' => function ($url, User $model, $key) {
                        $hasAccess = \app\models\UserAccess::find()
                            ->where(['owner_id' => \Yii::$app->user->id, 'viewer_id' => $model->id])
                            ->exists();

                        if (!$hasAccess && $model->id !== \Yii::$app->user->id) {
                            return Html::a(
                                'Дать доступ',
                                $url,
                                [
                                    'title' => 'Дать доступ к библиотеке',
                                    'data-confirm' => 'Вы уверены, что хотите дать доступ этому пользователю?',
                                    'data-method' => 'post',
                                    'class' => 'btn btn-success btn-sm',
                                ]
                            );
                        }
                        return ''; 
                    },
                    'view-library' => function ($url, User $model, $key) {
                        $hasAccess = \app\models\UserAccess::find()
                            ->where(['owner_id' => $model->id, 'viewer_id' => \Yii::$app->user->id])
                            ->exists();

                        if ($hasAccess && $model->id !== \Yii::$app->user->id) {
                            return Html::a(
                                'Посмотреть библиотеку',
                                $url,
                                [
                                    'title' => 'Посмотреть библиотеку пользователя',
                                    'class' => 'btn btn-info btn-sm',
                                ]
                            );
                        }
                        return '';
                    },
                ],
            ],
        ],
    ]); ?>


</div>
