<?php

namespace console\models;

use Yii;
use yii\web\User;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property string $user_id
 * @property string $created_at
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }

    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required', 'message' => 'Field cannot be blank', 'on' => 'create-assignment'],
            [['created_at'], 'safe'],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['item_name' => 'name']],
        ];
    }

    public function scenarios()
    {
        $scenarios =  parent::scenarios();
        $scenarios['create-assignment'] = ['item_name','user_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    public function getUser()
    {
        return $this->hasOne(\common\models\User::className(), ['id' => 'user_id']);

    }
}
