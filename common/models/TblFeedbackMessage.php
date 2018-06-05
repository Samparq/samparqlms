<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_feedback_message".
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property integer $sender_id
 * @property string $sender_name
 * @property string $feedback_message
 * @property string $date_time
 * @property integer $status
 * @property integer $feedback-type
 */
class TblFeedbackMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_feedback_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feedback_id', 'sender_id', 'status', 'feedback-type'], 'integer'],
            [['date_time'], 'safe'],
            [['sender_name', 'feedback_message'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'feedback_id' => 'Feedback ID',
            'sender_id' => 'Sender ID',
            'sender_name' => 'Sender Name',
            'feedback_message' => 'Feedback Message',
            'date_time' => 'Date Time',
            'status' => 'Status',
            'feedback-type' => 'Feedback Type',
        ];
    }
}
