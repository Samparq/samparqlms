<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tbl_post_attachments".
 *
 * @property integer $id
 * @property integer $post_id
 * @property string $attachment_name
 * @property string $orignal_name
 * @property integer $attachment_type
 * @property integer $attach_status
 * @property string $attach_path
 * @property string $attach_date
 * @property integer $remove_id
 * @property string $thumb_name
 * @property string $thumb_path
 */
class TblPostAttachments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_post_attachments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'attachment_name', 'orignal_name'], 'required'],
            [['post_id', 'attachment_type', 'attach_status', 'remove_id'], 'integer'],
            [['attach_date'], 'safe'],
            [['attachment_name', 'orignal_name', 'thumb_name'], 'string', 'max' => 200],
            [['attach_path', 'thumb_path'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'attachment_name' => 'Attachment Name',
            'orignal_name' => 'Orignal Name',
            'attachment_type' => 'Attachment Type',
            'attach_status' => 'Attach Status',
            'attach_path' => 'Attach Path',
            'attach_date' => 'Attach Date',
            'remove_id' => 'Remove ID',
            'thumb_name' => 'Thumb Name',
            'thumb_path' => 'Thumb Path',
        ];
    }
}
