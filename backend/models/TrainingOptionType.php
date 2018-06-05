<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "training_option_type".
 *
 * @property integer $id
 * @property string $type
 * @property integer $status
 * @property string $created_at
 */
class TrainingOptionType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'training_option_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['created_at'], 'safe'],
            [['type'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
