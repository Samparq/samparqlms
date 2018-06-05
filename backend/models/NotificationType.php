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
 * This is the model class for table "notification_type".

 * @property string $name
 * @property integer $status
 */
class NotificationType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */



    public static function tableName()
    {
        return 'notification_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['name'], 'string']
        ];
    }

}
