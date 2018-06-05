<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "license".
 *
 * @property integer $id
 * @property string $name
 * @property integer $per_user_cost
 * @property string $status
 */
class License extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'license';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['per_user_cost'], 'integer'],
            [['status'], 'string'],
            [['name'], 'string', 'max' => 45],
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
            'per_user_cost' => 'Per User Cost',
            'status' => 'Status',
        ];
    }
}
