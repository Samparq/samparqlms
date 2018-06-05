<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kudos_product_categories".
 *
 * @property integer $id
 * @property string $name
 * @property string $image
 * @property string $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class KudosProductCategories extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kudos_product_categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'string'],
            [['created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['description','name', 'image'], 'required', 'message' => 'Field can\'t be blank'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 45],
            [['image'], 'file', 'extensions' => 'png,jpeg,jpg']
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
            'image' => 'Image',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
