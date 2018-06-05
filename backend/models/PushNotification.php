<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 13/3/18
 * Time: 11:07 AM
 */


namespace backend\models;

use Yii;

/**
 * This is the model class for table "push_notification".

 * @property string $user_ids
 * @property string $text
 * @property integer $created_by
 */
class PushNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */



    public static function tableName()
    {
        return 'push_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by'], 'integer'],
            [['user_ids','text'], 'required'],
            [['user_ids'], 'safe'],
            [['text'], 'string', 'max' => 300]
        ];
    }

}
