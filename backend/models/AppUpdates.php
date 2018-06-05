<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "app_updates".
 *
 * @property integer $id
 * @property integer $version_code
 * @property float $version_name
 * @property string $update_date
 * @property integer $status
 */
class AppUpdates extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public $firstDig;
    public $secondDig;
    public $thirdDig;

    public static function tableName()
    {
        return 'app_updates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['update_date'], 'safe'],
            [['status','firstDig','secondDig','thirdDig','version_code'], 'integer'],
            [['firstDig','secondDig','thirdDig','version_code','version_name'], 'required'],
            [['version_code'], 'string', 'max' => 45],
            ['version_code', 'validateVersionCode'],
            [['version_name'], 'string', 'max' => 250],
        ];
    }

    public function validateVersionCode($attirbute, $params){
        $versionCode = $this->$attirbute;
        $versionModel = AppUpdates::findOne(['id' => 1]);
        if($versionCode <= $versionModel->version_code){
            $this->addError($attirbute, 'New version code must be greater than current version code');
        }
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version_code' => 'Version Code',
            'version_name' => 'Version Name',
            'update_date' => 'Update Date',
            'status' => 'Status',
            'secondDig' => '000',
            'firstDig' => '000',
            'thirdDig' => '000',
        ];
    }
}
