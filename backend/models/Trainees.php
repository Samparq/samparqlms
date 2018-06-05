<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "trainees".
 *
 * @property integer $id
 * @property integer $training_id
 * @property integer $user_id
 * @property string $username
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property integer $type
 */
class Trainees extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $progress;
    public $result;

    public static function tableName()
    {
        return 'trainees';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['training_id', 'type','status', 'created_by','notification_status','certificate_download'], 'integer'],
            [['created_at','training_sd','training_ed','progress','result'], 'safe'],
            [['user_id'], 'required','message' => 'Please add at least one trainee'],
            [['username'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'training_id' => 'Training ID',
            'user_id' => 'User ID',
            'username' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
