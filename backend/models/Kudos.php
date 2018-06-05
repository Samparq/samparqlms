<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kudos".
 *
 * @property integer $id
 * @property string $name
 * @property integer $value
 * @property string $template_id
 * @property string $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Kudos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kudos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value', 'created_by', 'updated_by'], 'integer'],
            [['status'], 'string'],
            [['name','value','template_id'], 'required', 'message' => 'Field can\'t be blank'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'template_id'], 'string', 'max' => 45],
            [['description'], 'string', 'max' => 255],
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
            'value' => 'Value',
            'template_id' => 'Template ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
