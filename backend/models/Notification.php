<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 13/3/18
 * Time: 11:03 AM
 */

namespace backend\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property integer $type_id
 * @property integer $post_id
 * @property integer $user_id
 * @property integer $sender_id
 * @property string $message
 * @property integer $status
 * @property integer $read_status
 * @property integer $seen_status
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */



    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'user_id','sender_id','status','read_status','seen_status'], 'integer'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

}
