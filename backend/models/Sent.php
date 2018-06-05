<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sent".
 *
 * @property string $id
 * @property string $mail_to
 * @property string $to_detail
 * @property integer $mail_from
 * @property integer $flag
 * @property string $process_date
 * @property integer $status
 * @property integer $file_status
 * @property string $subject
 * @property string $message
 */
class Sent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mail_from', 'flag', 'status', 'file_status'], 'integer'],
            [['process_date'], 'safe'],
            [['message'], 'string'],
            [['mail_to', 'to_detail', 'subject'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mail_to' => 'Mail To',
            'to_detail' => 'To Detail',
            'mail_from' => 'Mail From',
            'flag' => 'Flag',
            'process_date' => 'Process Date',
            'status' => 'Status',
            'file_status' => 'File Status',
            'subject' => 'Subject',
            'message' => 'Message',
        ];
    }
}
