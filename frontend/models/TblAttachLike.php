<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tbl_attach_like".
 *
 * @property integer $id
 * @property integer $attach_id
 * @property integer $post_id
 * @property integer $like_userid
 * @property string $like_username
 * @property integer $like_status
 * @property string $like_date
 */
class TblAttachLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_attach_like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attach_id', 'post_id', 'like_userid', 'like_username'], 'required'],
            [['attach_id', 'post_id', 'like_userid', 'like_status'], 'integer'],
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
            'attach_id' => 'Attach ID',
            'post_id' => 'Post ID',
            'like_userid' => 'Like Userid',
            'like_username' => 'Like Username',
            'like_status' => 'Like Status',
            'like_date' => 'Like Date',
        ];
    }
}
