<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_feedback_member".
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property integer $user_id
 * @property string $user_name
 * @property integer $status
 * @property integer $is_admin
 */
class TblFeedbackMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_feedback_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feedback_id', 'user_id', 'status', 'is_admin','feedback_type'], 'integer'],
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
            'feedback_id' => 'Feedback ID',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'status' => 'Status',
            'is_admin' => 'Is Admin',
            'feedback_type'=>'Feedback Type',
        ];
    }
}
