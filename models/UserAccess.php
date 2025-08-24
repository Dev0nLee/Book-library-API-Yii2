<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_access".
 *
 * @property int $id
 * @property int $owner_id
 * @property int $viewer_id
 *
 * @property User $owner
 * @property User $viewer
 */
class UserAccess extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'viewer_id'], 'required'],
            [['owner_id', 'viewer_id'], 'integer'],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['owner_id' => 'id']],
            [['viewer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['viewer_id' => 'id']],
            [['owner_id', 'viewer_id'], 'unique', 'targetAttribute' => ['owner_id', 'viewer_id'], 'message' => 'Доступ уже предоставлен.'],
            ['owner_id', 'compare', 'compareAttribute' => 'viewer_id', 'operator' => '!=', 'message' => 'Нельзя предоставить доступ самому себе.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'viewer_id' => 'Viewer ID',
        ];
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[Viewer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getViewer()
    {
        return $this->hasOne(User::class, ['id' => 'viewer_id']);
    }

}
