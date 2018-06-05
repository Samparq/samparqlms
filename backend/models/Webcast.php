<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "webcast".
 *
 * @property integer $id
 * @property string $url
 * @property string $start_date
 * @property integer $status
 * @property integer $live_status
 * @property string $created_at
 * @property string $updated_at
 */
class Webcast extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webcast';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at','url','live_status'], 'safe'],
            [['start_date','status'], 'required', 'message' => 'field cannot be blank'],
            [['status'], 'integer'],
            [['start_date'], 'validateStartDate'],
            [['live_status'], 'validateLiveStatus', 'params' => ['status' => 'status', 'url' => 'url']],
        ];
    }

    public function validateLiveStatus($attribute, $params){
        $liveStatus = $this->$attribute;
        $url = $this->$params["url"];
        $status = $this->$params["status"];
        if(($liveStatus == 1) && (empty($status) || $status != 1 || empty($url))){
            $this->addError($attribute, "Webcast cannot be live without making status active");
        }
    }


    public function validateStartDate($attribute){
        $startDate = $this->$attribute;
        $currentDateTime = date("Y-m-d H:i");
        if($startDate<$currentDateTime){
            $this->addError($attribute, 'Start date must be greater than current date');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Embed Code',
            'start_date' => 'Start Date',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
