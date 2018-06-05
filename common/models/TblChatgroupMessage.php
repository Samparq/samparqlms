<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_chatgroup_message".
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $sender_id
 * @property string $sender_name
 * @property string $chat_message
 * @property integer $status
 * @property integer $att_status
 * @property string $date_time
 */
class TblChatgroupMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_chatgroup_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'sender_id', 'status', 'att_status', 'chat_type'], 'integer'],
            [['chat_message'], 'string'],
            [['date_time'], 'safe'],
            [['sender_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'sender_id' => 'Sender ID',
            'sender_name' => 'Sender Name',
            'chat_message' => 'Chat Message',
            'status' => 'Status',
            'att_status' => 'Att Status',
            'date_time' => 'Date Time',
            'chat_type' => 'Chat Type',
        ];
    }
}
