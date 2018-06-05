<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "kudos_products".
 *
 * @property integer $id
 * @property string $name
 * @property integer $kpc_id
 * @property string $image
 * @property integer $point
 * @property string $status
 * @property integer $quantity
 * @property integer $value
 * @property string $stock_status
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class KudosProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kudos_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kpc_id', 'point', 'quantity', 'value', 'created_by', 'updated_by'], 'integer'],
            [['status', 'stock_status','description'], 'string'],
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
            'kpc_id' => 'Kpc ID',
            'image' => 'Image',
            'point' => 'Point',
            'status' => 'Status',
            'quantity' => 'Quantity',
            'value' => 'Value',
            'stock_status' => 'Stock Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
