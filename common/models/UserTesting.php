<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_chatgroup".
 *
 * @property integer $id
 * @property string $name
 * @property string $icon_orignal
 * @property string $icon_thumb
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $deleted_on
 * @property integer $deleted_by
 * @property integer $status
 */
class UserTesting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'userTesting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'safe']
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
            'icon_orignal' => 'Icon Orignal',
            'icon_thumb' => 'Icon Thumb',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_on' => 'Deleted On',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
        ];
    }
}
