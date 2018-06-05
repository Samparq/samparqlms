<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "login_log".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $username
 * @property string $login_time
 * @property string $logout_time
 * @property string $total_time
 * @property string $created_at
 * @property string $created_by
 * @property string last_activity
 */
class LoginLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public $employee_id;

    public static function tableName()
    {
        return 'login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid'], 'integer'],
            [['login_time','employee_id', 'logout_time', 'last_activity','created_at'], 'safe'],
            [['username'], 'string', 'max' => 100],
            [['total_time', 'created_by'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userid' => 'Userid',
            'username' => 'Username',
            'login_time' => 'Login Time',
            'last_activity' => 'Last Activity',
            'logout_time' => 'Logout Time',
            'total_time' => 'Total Time',
            'created_at' => 'Log Creation Time',
            'created_by' => 'Log Created By',
        ];
    }
}
