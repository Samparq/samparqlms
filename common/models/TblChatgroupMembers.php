<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_chatgroup_members".
 *
 * @property integer $id
 * @property integer $group_id
 * @property integer $user_id
 * @property string $user_name
 * @property integer $status
 * @property integer $is_admin
 * @property integer $updated_by
 * @property integer $created_by
 */
class TblChatgroupMembers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_chatgroup_members';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'user_id', 'status', 'is_admin', 'updated_by', 'created_by'], 'integer'],
            [['user_name'], 'string', 'max' => 200],
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
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'status' => 'Status',
            'is_admin' => 'Is Admin',
            'updated_by' => 'Updated By',
            'created_by' => 'Created By',
        ];
    }
}
