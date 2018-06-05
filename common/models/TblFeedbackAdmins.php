<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_feedback_admins".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_name
 * @property string $created_at
 * @property integer $status
 * @property integer $is_admin
 */
class TblFeedbackAdmins extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_feedback_admins';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'is_admin'], 'integer'],
            [['created_at'], 'safe'],
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
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'created_at' => 'Created At',
            'status' => 'Status',
            'is_admin' => 'Is Admin',
        ];
    }
}
