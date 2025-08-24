<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property int $user_id
 * @property int $status
 * @property User $user
 */
class Book extends \yii\db\ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $textFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => 1],
            [['title', 'text', 'user_id'], 'required'],
            [['text'], 'string'],
            [['user_id', 'status'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['title'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['textFile'], 'file', 'extensions' => 'txt,doc,docx,pdf,rtf', 'maxSize' => 1024 * 1024 * 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'title' => 'Название',
            'text' => 'Содержание',
            'user_id' => 'User ID',
            'status' => 'Status',
            'textFile' => 'Загрузить файл с текстом',
        ];
    }

    /**
     * Read text from file
     * @return string|false
     */
    public function readTextFromFile()
    {
        if (!$this->textFile) {
            return false;
        }

        $extension = strtolower($this->textFile->extension);
        
        if ($extension == 'txt') {
            return $this->readTxtFile();
        } else {
            return false;
        }

    }

    /**
     * Read File .txt
     * @return string|false
     */
    private function readTxtFile()
    {
        $content = file_get_contents($this->textFile->tempName);
        return $content !== false ? $content : false;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id']);
    }

}
