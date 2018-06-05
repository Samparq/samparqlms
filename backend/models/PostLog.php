<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "post_log".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $username
 * @property integer $post_id
 * @property integer $comment_id
 * @property string $created_at
 * @property string $created_by
 */
class PostLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'post_id', 'comment_id'], 'integer'],
            [['created_at','description'], 'safe'],
            [['username', 'created_by'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userid' => 'Userid',
            'username' => 'Username',
            'post_id' => 'Post ID',
            'comment_id' => 'Comment ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
