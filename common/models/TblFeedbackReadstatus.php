<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_feedback_readstatus".
 *
 * @property integer $id
 * @property integer $message_id
 * @property integer $feedback_id
 * @property integer $user_id
 * @property integer $status
 * @property string $date_time
 */
class TblFeedbackReadstatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_feedback_readstatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'feedback_id', 'user_id', 'status'], 'integer'],
            [['date_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'feedback_id' => 'Feedback ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'date_time' => 'Date Time',
        ];
    }
}