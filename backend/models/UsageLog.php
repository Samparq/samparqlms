<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 13/3/18
 * Time: 12:44 PM
 */

namespace backend\models;

use Yii;

/**
 * This is the model class for table "usage_log".
 *
 * @property integer $id
 * @property integer $userid
 * @property string $username
 * @property string $login_time
 * @property string $logout_time
 * @property string $total_time
 * @property string $created_at
 * @property string $created_by
 * @property string last_activity
 */
class UsageLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */


    public static function tableName()
    {
        return 'usage_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid'], 'integer'],
            [['log_creation_time'], 'safe']
        ];
    }

}
