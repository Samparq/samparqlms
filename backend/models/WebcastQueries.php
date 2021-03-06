<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "webcast_queries".
 *
 * @property integer $id
 * @property integer $webcast_id
 * @property string $name
 * @property integer $regid
 * @property string $query
 * @property string $created_time
 * @property string $location
 * @property string $phone
 * @property string $email_id
 * @property string $query_type
 * @property string $empid
 */
class WebcastQueries extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webcast_queries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['webcast_id','notification_status', 'regid'], 'integer'],
            [['query'], 'required'],
            [['query'], 'string'],
            [['created_time'], 'safe'],
            [['name', 'location'], 'string', 'max' => 500],
            [['phone'], 'string', 'max' => 100],
            [['email_id'], 'string', 'max' => 250],
            [['query_type'], 'string', 'max' => 45],
            [['empid'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'webcast_id' => 'Webcast ID',
            'name' => 'Name',
            'regid' => 'Regid',
            'query' => 'Query',
            'created_time' => 'Created Time',
            'location' => 'Location',
            'phone' => 'Phone',
            'email_id' => 'Email ID',
            'query_type' => 'Query Type',
            'empid' => 'Empid',
        ];
    }


    public function getThread(){
        return $this->hasMany(WebcastQueryThread::className(), ['webcast_query_id' => 'id']);
    }

    public function getWebcast(){
        return $this->hasOne(Webcast::className(), ['id' => 'webcast_id']);
    }
}
