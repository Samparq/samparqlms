<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kudos_redemption".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $kudos_id
 * @property integer $kudos_product_id
 * @property string $redemption_date
 */
class KudosRedemption extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kudos_redemption';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'kudos_id', 'kudos_product_id'], 'integer'],
            [['redemption_date'], 'safe'],
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
            'kudos_product_id' => 'Kudos Product ID',
            'redemption_date' => 'Redemption Date',
        ];
    }

    public function getProductDetails(){
        return $this->hasOne(KudosProducts::className(), ['id' => 'kudos_product_id']);
    }

    public function getKudosDetails(){
        return $this->hasOne(Kudos::className(), ['id' => 'kudos_id']);
    }
}
