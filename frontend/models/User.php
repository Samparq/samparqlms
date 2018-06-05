<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property integer $user_type
 * @property string $name
 * @property string $last_name
 * @property string $flag
 * @property integer $mobile_permission
 * @property string $imei_app
 * @property string $app_regid
 * @property string $employee_id
 * @property string $dob
 * @property string $image_path
 * @property string $image_name
 * @property string $mobile_no
 * @property integer $key
 * @property integer $update_status
 * @property integer $form_submit
 * @property integer $email_conf
 * @property integer $super_admin
 * @property string $lastlogin_time
 * @property string $test
 * @property string $auth_token
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'user_type', 'mobile_permission', 'key', 'update_status', 'form_submit', 'email_conf', 'super_admin'], 'integer'],
            [['created_at', 'updated_at', 'dob', 'lastlogin_time'], 'safe'],
            [['flag', 'app_regid', 'auth_token'], 'string'],
           // [['test'], 'required'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['name', 'last_name'], 'string', 'max' => 100],
            [['imei_app', 'employee_id', 'mobile_no', 'test'], 'string', 'max' => 45],
            [['image_path', 'image_name'], 'string', 'max' => 250],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'user_type' => 'User Type',
            'name' => 'Name',
            'last_name' => 'Last Name',
            'flag' => 'Flag',
            'mobile_permission' => 'Mobile Permission',
            'imei_app' => 'Imei App',
            'app_regid' => 'App Regid',
            'employee_id' => 'Employee ID',
            'dob' => 'Dob',
            'image_path' => 'Image Path',
            'image_name' => 'Image Name',
            'mobile_no' => 'Mobile No',
            'key' => 'Key',
            'update_status' => 'Update Status',
            'form_submit' => 'Form Submit',
            'email_conf' => 'Email Conf',
            'super_admin' => 'Super Admin',
            'lastlogin_time' => 'Lastlogin Time',
           // 'test' => 'Test',
            'auth_token' => 'Auth Token',
        ];
    }
}
