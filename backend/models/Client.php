<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property integer $id
 * @property string $name
 * @property integer $license_id
 * @property integer $server_id
 * @property string $code
 * @property integer $no_of_users
 * @property string $subscription_sd
 * @property string $subscription_ed
 * @property string $status
 * @property string $created_at
 * @property integer $created_by
 */
class Client extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public $email;
    public $months;


    public static function getDb(){
        return Yii::$app->get('db');
    }


    public static function tableName()
    {
        return 'client';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_id', 'server_id', 'no_of_users', 'created_by', 'cost_per_user','months'], 'integer'],
            [['name','code','cost_per_user','license_id','email', 'no_of_users', 'created_by','subscription_sd', 'subscription_ed','months'], 'required', 'message' => 'Field can\'t be blank'],
            [['subscription_sd', 'subscription_ed', 'created_at','remark'], 'safe'],
            [['status'], 'string'],
            [['email'], 'email'],
            [['name'], 'unique', 'message' => 'Client with this name is already exist'],
            [['name', 'code'], 'string', 'max' => 45],
        ];
    }




    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'license_id' => 'License name',
            'server_id' => 'Server ID',
            'code' => 'Code',
            'cost_per_user' => 'Cost Per User',
            'no_of_users' => 'No of users',
            'subscription_sd' => 'Subscription start date',
            'subscription_ed' => 'Subscription end date',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
