<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tbl_post".
 *
 * @property string $id
 * @property integer $post_userid
 * @property string $post_sendername
 * @property string $post_description
 * @property integer $attach_status
 * @property string $post_datetime
 * @property integer $post_status
 * @property integer $remove_id
 */
class TblPost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_userid', 'attach_status', 'post_status', 'remove_id'], 'integer'],
            [['post_datetime','post_description'], 'safe'],
            [['post_sendername'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_userid' => 'Post Userid',
            'post_sendername' => 'Post Sendername',
            'post_description' => 'Post Description',
            'attach_status' => 'Attach Status',
            'post_datetime' => 'Post Datetime',
            'post_status' => 'Post Status',
            'remove_id' => 'Remove ID',
        ];
    }
}
