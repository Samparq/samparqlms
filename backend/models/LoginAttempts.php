<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "login_attempts".
 *
 * @property integer $id
 * @property integer $status
 * @property string $ipaddress
 * @property string $username
 * @property string $password
 * @property string $attempt_time
 * @property string $attempt_frequency
 * @property string $created_at
 * @property string $created_by
 */
class LoginAttempts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_attempts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'attempt_time','ipaddress','status', 'created_at'], 'safe'],
            [['username'], 'string', 'max' => 100],
            [['created_by'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ipaddress' => 'Ipaddress',
            'username' => 'Username',
            'password' => 'Password',
            'attempt_time' => 'Attempt Time',
            'attempt_frequency' => 'Attempt Frequency',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
