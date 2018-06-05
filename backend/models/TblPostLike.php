<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_post_like".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $like_userid
 * @property string $like_username
 * @property integer $like_status
 * @property string $like_date
 */
class TblPostLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_post_like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'like_userid', 'like_username'], 'required'],
            [['post_id', 'like_userid', 'like_status'], 'integer'],
            [['like_date'], 'safe'],
            [['like_username'], 'string', 'max' => 200],
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
            'like_userid' => 'Like Userid',
            'like_username' => 'Like Username',
            'like_status' => 'Like Status',
            'like_date' => 'Like Date',
        ];
    }
}
