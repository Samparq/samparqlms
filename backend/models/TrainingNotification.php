<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "training_notification".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $user_id
 * @property integer $read_status
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 */
class TrainingNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'training_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'read_status', 'status', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['title','user_id','description'],'required', 'message' => 'Required*'],
            [['title'], 'string', 'max' => 80],
            [['description'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'User ID',
            'read_status' => 'Read Status',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
