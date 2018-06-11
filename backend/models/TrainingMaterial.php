<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "training_material".
 *
 * @property integer $id
 * @property integer $training_id
 * @property string $original_name
 * @property string $new_name
 * @property string $path
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property integer $created_by
 */
class TrainingMaterial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */


    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }


    public static function tableName()
    {
        return 'training_material';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['training_id', 'type', 'created_by'], 'integer'],
            [['created_at','status','download_status'], 'safe'],
            [['original_name', 'new_name', 'path'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'training_id' => 'Training ID',
            'original_name' => 'Original Name',
            'new_name' => 'New Name',
            'path' => 'Path',
            'type' => 'Type',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
