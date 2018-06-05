<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_chatgroup_attach".
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $sender_id
 * @property integer $message_id
 * @property string $orignal_name
 * @property string $new_name
 * @property string $thumb_name
 * @property string $date_time
 * @property integer $status
 */
class TblChatgroupAttach extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_chatgroup_attach';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'sender_id', 'message_id', 'status'], 'integer'],
            [['date_time'], 'safe'],
            [['orignal_name', 'new_name', 'thumb_name','type'], 'string', 'max' => 200],
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
            'message_id' => 'Message ID',
            'orignal_name' => 'Orignal Name',
            'new_name' => 'New Name',
            'thumb_name' => 'Thumb Name',
            'date_time' => 'Date Time',
            'status' => 'Status',
        ];
    }
}
