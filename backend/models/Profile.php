<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property integer $id
 * @property string $type
 * @property integer $status
 * @property string $created_on
 * @property integer $created_by
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'created_by'], 'integer'],
            [['created_on'], 'safe'],
            [['type'], 'string', 'max' => 100],
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
            'created_on' => 'Created On',
            'created_by' => 'Created By',
        ];
    }
}
