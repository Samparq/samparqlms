<?php

namespace backend\models;

use backend\models\TblAttachComment;
use backend\models\TblPostAttachments;
use backend\models\TblPostComment;
use backend\models\TblPostLike;
use Yii;

/**
 * This is the model class for table "tbl_post".
 *
 * @property integer $id
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
    public $fileScript;
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
            [['post_description'], 'string'],
            [['post_datetime','fileScript'], 'safe'],
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
            'post_userid' => 'Userid',
            'post_sendername' => 'Sendername',
            'post_description' => 'Description',
            'attach_status' => 'Attach Status',
            'post_datetime' => 'Created On',
            'post_status' => 'Post Status',
            'remove_id' => 'Remove ID',
        ];
    }



    public function getAttachmentComments(){
        return $this->hasMany(TblAttachComment::className(), ['post_id' => 'id']);
    }

    public function getComments(){
        return $this->hasMany(TblPostComment::className(), ['post_id' => 'id']);
    }

    public function getLikes(){
        return $this->hasMany(TblPostLike::className(), ['post_id' => 'id']);
    }

    public function getAttachments(){
        return $this->hasMany(TblPostAttachments::className(), ['post_id' => 'id']);
    }


}
