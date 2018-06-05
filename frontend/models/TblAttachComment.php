<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tbl_attach_comment".
 *
 * @property integer $id
 * @property integer $attach_id
 * @property integer $post_id
 * @property integer $comment_userid
 * @property string $comment_username
 * @property string $comment_text
 * @property string $comment_date
 * @property integer $comment_status
 * @property integer $remove_id
 */
class TblAttachComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_attach_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attach_id', 'post_id', 'comment_userid', 'comment_username', 'comment_text'], 'required'],
            [['attach_id', 'post_id', 'comment_userid', 'comment_status', 'remove_id'], 'integer'],
            [['comment_text'], 'string'],
            [['comment_date'], 'safe'],
            [['comment_username'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attach_id' => 'Attach ID',
            'post_id' => 'Post ID',
            'comment_userid' => 'Comment Userid',
            'comment_username' => 'Comment Username',
            'comment_text' => 'Comment Text',
            'comment_date' => 'Comment Date',
            'comment_status' => 'Comment Status',
            'remove_id' => 'Remove ID',
        ];
    }
}
