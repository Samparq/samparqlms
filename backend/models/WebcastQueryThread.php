<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/2/18
 * Time: 4:43 PM
 */

namespace backend\models;

use Yii;

/**
 * This is the model class for table "webcast_query_thread".
 *
 * @property integer $id
 * @property integer $webcast_query_id
 * @property integer $notification_status
 * @property string $message
 * @property string $created_time
 * @property string $sender_id
 * @property string $receiver_id
 */

class WebcastQueryThread extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webcast_query_thread';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['webcast_query_id','notification_status','read_status'], 'integer'],
            [['message','sender_id','receiver_id'], 'string'],
            [['created_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'webcast_query_id' => 'Webcast ID',
            'message' => 'Reply',
            'created_time' => 'Created Time'
        ];
    }
}
