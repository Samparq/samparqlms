<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tbl_favourite_post".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $user_id
 * @property string $datetime
 * @property integer $status
 */
class TblFavouritePost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_favourite_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'user_id', 'status'], 'integer'],
            [['datetime'], 'safe'],
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
            'user_id' => 'User ID',
            'datetime' => 'Datetime',
            'status' => 'Status',
        ];
    }
}
