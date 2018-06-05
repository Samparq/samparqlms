<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "t_chat".
 *
 * @property string $id
 * @property string $sender_id
 * @property string $receiver_id
 * @property string $message
 * @property integer $training_id
 * @property integer $receiver_flag
 * @property integer $file_status
 * @property integer $read_status
 * @property integer $attachment_status
 * @property integer $attachment_type
 * @property integer $status
 * @property string $file_name
 * @property string $file_extention
 * @property string $original_filename
 * @property string $new_filename
 * @property string $file_path
 * @property string $created_at
 */
class Chat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_chat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sender_id', 'receiver_id', 'training_id', 'read_status', 'status', 'attachment_status','attachment_type'], 'integer'],
            [['message'], 'required'],
            [['message'], 'string'],
            [['original_filename', 'new_filename', 'file_path'], 'string', 'max' => 250],
            [['created_at'], 'string', 'max' => 100],
        ];
    }


}
