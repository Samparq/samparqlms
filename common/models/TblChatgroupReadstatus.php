<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_chatgroup_readstatus".
 *
 * @property integer $id
 * @property integer $message_id
 * @property integer $group_id
 * @property integer $user_id
 * @property integer $status
 * @property string $date_time
 */
class TblChatgroupReadstatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_chatgroup_readstatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'group_id', 'user_id', 'status'], 'integer'],
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
            'group_id' => 'Group ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'date_time' => 'Date Time',
            'member_id' => 'Member ID',
        ];
    }
}
