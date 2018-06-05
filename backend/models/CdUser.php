<?php

namespace backend\models;

use Yii;


class CdUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function getDb() {
        return Yii::$app->get('cosecDb');
    }


    public static function tableName()
    {
        return 'cd_user';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['id','active'], 'integer'],
            [['employee_id','aadhar_no','name','phone','marital_status','branch','department','designation','reporting_in_charge1','reporting_in_charge2','category','gender','pan','email'], 'string'],
            [['joining_date','confirmation_date','dob'], 'safe'],
        ];
    }
}
