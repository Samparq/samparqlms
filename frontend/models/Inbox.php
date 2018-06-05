<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "inbox".
 *
 * @property string $id
 * @property string $sent_id
 * @property string $mail_to
 * @property string $mail_from
 * @property string $subject
 * @property string $message
 * @property integer $flag
 * @property integer $file_status
 * @property string $process_date
 * @property integer $created_by
 * @property string $updated_on
 * @property string $mail_to_userid
 */
class Inbox extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'inbox';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sent_id', 'mail_to', 'mail_from', 'flag', 'file_status', 'created_by', 'mail_to_userid'], 'integer'],
            [['message'], 'string'],
            [['process_date', 'updated_on'], 'safe'],
            [['subject'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sent_id' => 'Sent ID',
            'mail_to' => 'Mail To',
            'mail_from' => 'Mail From',
            'subject' => 'Subject',
            'message' => 'Message',
            'flag' => 'Flag',
            'file_status' => 'File Status',
            'process_date' => 'Process Date',
            'created_by' => 'Created By',
            'updated_on' => 'Updated On',
            'mail_to_userid' => 'Mail To Userid',
        ];
    }
}
