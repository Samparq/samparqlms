<?php

namespace frontend\models;

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
class TblFeedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sender_id'], 'required'],
            [['sender_id'], 'integer'],
            [['date_time'], 'safe'],
            [['sender_name'], 'string', 'max' => 250],
            [['feedback_str'], 'string', 'max' => 500],
            [['status'],'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sender_id' => 'Sender ID',
            'sender_name' => 'Sender Name',
            'feedback_str' => 'Feedback Str',
            'date_time' => 'Date Time',
            'status'=>'Status',
        ];
    }
}
