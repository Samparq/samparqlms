<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "feedback".
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
class WebcastQuery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function getDb() {
        return Yii::$app->get('secondaryDb');
    }


    public static function tableName()
    {
        return 'feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'regid'], 'integer'],
            [['query'], 'required'],
            [['query'], 'string'],
            [['created_time','query_from'], 'safe'],
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
}
