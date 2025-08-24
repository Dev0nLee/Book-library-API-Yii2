<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'textFile')->fileInput(['class' => 'form-control-file']) ?>

    <?= $form->field($model, 'text')->textarea(['rows' => 6, 'id' => 'book-text']) ?>

    <div class="form-group mb-3">
        <?= Html::button('Прочитать текст из файла', [
            'class' => 'btn btn-info',
            'onclick' => 'readTextFromFile()'
        ]) ?>
        <small class="form-text text-muted">
            Поддерживаемые форматы: TXT (максимум 5MB)
        </small>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
function readTextFromFile() {
    const fileInput = document.querySelector('input[type="file"]');
    const textArea = document.getElementById('book-text');
    
    if (!fileInput.files[0]) {
        alert('Пожалуйста, выберите файл для загрузки');
        return;
    }
    
    const file = fileInput.files[0];
    
    const formData = new FormData();
    formData.append('Book[textFile]', file);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_csrf"]')?.value;
    if (csrfToken) {
        formData.append('_csrf', csrfToken);
    }
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Читаю файл...';
    button.disabled = true;
    
    fetch('<?= \yii\helpers\Url::to(['book/read-file']) ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            textArea.value = data.text;
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при чтении файла');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}
</script>
