<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kudos_points".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $kudos_id
 * @property string $point
 * @property string $earned_date
 * @property string $expiry_date
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class KudosPoints extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kudos_points';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'kudos_id', 'created_by', 'updated_by'], 'integer'],
            [['earned_date', 'expiry_date', 'created_at', 'updated_at'], 'safe'],
            [['point'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'kudos_id' => 'Kudos ID',
            'point' => 'Point',
            'earned_date' => 'Earned Date',
            'expiry_date' => 'Expiry Date',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getKudosDetails(){
        return $this->hasOne(Kudos::className(), ['id' => 'kudos_id']);
    }
}
