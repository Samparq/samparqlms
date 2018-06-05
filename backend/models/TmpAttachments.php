<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 5/9/17
 * Time: 3:27 PM
 */


namespace backend\models;

use Yii;

/**
 * This is the model class for table "tmp_attachments".
 *
 * @property string $id
 * @property string $file_script
 * @property string $file_name
 * @property string $file_path
 * @property string $orignal_filename
 * @property string $ext
 * @property string $upload_filescol
 * @property string $inbox_id
 * @property string $sent_id
 */
class TmpAttachments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tmp_attachments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_script'], 'safe'],
            [['file_name', 'file_path', 'orignal_filename'], 'string', 'max' => 250],
            [['ext'], 'string', 'max' => 100]
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
            'file_path' => 'File Path',
            'orignal_filename' => 'Orignal Filename',
            'ext' => 'Ext',
        ];
    }
}
