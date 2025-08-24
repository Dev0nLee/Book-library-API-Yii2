<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $password
 *
 * @property Book[] $books
 * @property UserAccess[] $userAccesses
 * @property UserAccess[] $userAccesses0
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    public $password_repeat;
    public $rules;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password'], 'string', 'min' => 6],
            [['name'], 'unique'],
            ['name', 'match', 'pattern' => '/^[a-zA-Z0-9-]*$/i', 'message' =>
           'Разрешены только латиница, цифры или тире'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            ['rules', 'compare', 'compareValue' => 1, 'message' => 'Необходимо
            принять условия регистрации'],
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Логин',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
        ];
    }

    /**
     * Gets query for [[Books]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Book::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAccesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccesses()
    {
        return $this->hasMany(UserAccess::class, ['owner_id' => 'id']);
    }

    /**
     * Gets query for [[UserAccesses0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccesses0()
    {
        return $this->hasMany(UserAccess::class, ['viewer_id' => 'id']);
    }

    /**
    * Finds an identity by the given ID.
    *
    * @param string|int $id the ID to be looked for
    * @return IdentityInterface|null the identity object that matches the given ID.
    */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
    * Finds an identity by the given token.
    *
    * @param string $token the token to be looked for
    * @return IdentityInterface|null the identity object that matches the given token.
    */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
    * @return int|string current user ID
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * @return string|null current user auth key
    */
    public function getAuthKey()
    {
        return null;
    }

    /**
    * @param string $authKey
    * @return bool|null if auth key is valid for current user
    */
    public function validateAuthKey($authKey)
    {
        return false;
    }

    /**
    * Finds user by username
    *
    * @param string $username
    * @return static|null
    */
    public static function findByUsername($username)
    {
        return User::findOne(['name' => $username]);;
    }

    /**
    * Validates password
    *
    * @param string $password password to validate
    * @return bool if password provided is valid for current user
    */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }
    public function beforeSave($insert)
    { $this->password = md5($this->password);
        return parent::beforeSave($insert);
    }
}
