<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "upload_files".
 *
 * @property string $id
 * @property integer $mail_to
 * @property integer $mail_from
 * @property string $process_date
 * @property string $file_name
 * @property string $file_path
 * @property string $orignal_filename
 * @property string $ext
 * @property string $upload_filescol
 * @property string $inbox_id
 * @property string $sent_id
 */
class UploadFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'upload_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mail_to', 'mail_from', 'inbox_id', 'sent_id'], 'integer'],
            [['process_date'], 'safe'],
            [['file_name', 'file_path', 'orignal_filename'], 'string', 'max' => 250],
            [['ext'], 'string', 'max' => 100],
            [['upload_filescol'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mail_to' => 'Mail To',
            'mail_from' => 'Mail From',
            'process_date' => 'Process Date',
            'file_name' => 'File Name',
            'file_path' => 'File Path',
            'orignal_filename' => 'Orignal Filename',
            'ext' => 'Ext',
            'upload_filescol' => 'Upload Filescol',
            'inbox_id' => 'Inbox ID',
            'sent_id' => 'Sent ID',
        ];
    }
}
