<?php

namespace backend\models;

use common\models\User;
use Yii;

/**
 * This is the model class for table "group_members".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $group_id
 * @property string $status
 * @property integer $added_by
 * @property string $added_at
 */
class GroupMembers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */


    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }


    public static function tableName()
    {
        return 'group_members';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'added_by'], 'integer'],
            [['status'], 'string'],
            [['user_id'], 'required', 'message' => 'Field can\'t be blank'],
            ['added_by', 'default', 'value' => Yii::$app->user->id],
            [['added_at','user_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'group_id' => 'Group ID',
            'status' => 'Status',
            'added_by' => 'Added By',
            'added_at' => 'Added At',
        ];
    }

    public function getUsers(){
        return $this->hasOne(User::className(), ['id' => 'user_id'])->andOnCondition(['user.flag' => 'ACTIVE']);
    }
}
