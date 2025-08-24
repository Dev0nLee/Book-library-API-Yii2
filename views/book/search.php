<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJsFile('@web/js/main.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs(
    "var bookSearchAjaxUrl = '" . Url::to(['book/search-ajax']) . "';" .
    "var bookSaveAjaxUrl = '" . Url::to(['book/save-book-ajax']) . "';" .
    "var csrfParam = '" . Yii::$app->request->csrfParam . "';" .
    "var csrfToken = '" . Yii::$app->request->csrfToken . "';",
    \yii\web\View::POS_HEAD
);
?>

<div class="book-search">
    <h1>Поиск книг</h1>

    <div class="search-form">
        <div class="input-group mb-3">
            <input type="text" id="searchQuery" class="form-control" placeholder="Введите название книги">
            <div class="input-group-append">
                <button class="btn btn-primary" onclick="searchBooks()">Поиск</button>
            </div>
        </div>
    </div>

    <div id="searchResults" class="mt-4">
        
    </div>
</div>

