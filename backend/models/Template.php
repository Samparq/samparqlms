<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_feedback".
 *
 * @property integer $id
 * @property integer $sender_id
 * @property string $sender_name
 * @property string $feedback_str
 * @property string $date_time
 */
class Template extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'templates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_path'], 'safe'],
            [['use_status','status'], 'integer'],
            [['template_name'], 'string', 'max' => 250],
            [['feedback_str'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sender_id' => 'User id',
            'sender_name' => 'User Name',
            'feedback_str' => 'Feedback',
            'date_time' => 'Feedback Time',
        ];
    }
}
