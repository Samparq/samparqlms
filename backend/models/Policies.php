<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "policies".
 *
 * @property string $id
 * @property string $file_name
 * @property string $orignal_filename
 * @property string $file_path
 * @property string $ext
 * @property string $file_size
 * @property string $created_by
 * @property string $process_date
 * @property integer $profile_id
 * @property string $description
 * @property string $title
 * @property integer $flag
 * @property integer $policy_type
 */

class Policies extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'profile_id', 'flag', 'policy_type'], 'integer'],
            [['process_date'], 'safe'],
            [['file_name','policy_type', 'description', 'title'], 'required'],
            ['file_name', 'file','extensions' => 'pdf'],
            [['orignal_filename', 'file_path', 'description', 'title'], 'string', 'max' => 250],
            [['ext', 'file_size'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_name' => 'File Name',
            'orignal_filename' => 'Orignal Filename',
            'file_path' => 'File Path',
            'ext' => 'Ext',
            'file_size' => 'File Size',
            'created_by' => 'Created By',
            'process_date' => 'Process Date',
            'profile_id' => 'Profile ID',
            'description' => 'Description',
            'title' => 'Title',
            'flag' => 'Status',
            'policy_type' => 'Policy Type',
        ];
    }
}
