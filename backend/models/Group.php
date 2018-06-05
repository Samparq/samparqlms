<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "group".
 *
 * @property integer $id
 * @property string $name
 * @property string $status
 * @property string $created_at
 * @property integer $created_by
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */




    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }

    public static function tableName()
    {
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            ['created_by', 'default', 'value' => Yii::$app->user->id],
            [['name'], 'required', 'message' => 'Field can\'t be blank'],
            [['name', 'status'], 'string', 'max' => 45],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    public function getMembers(){
        return $this->hasMany(GroupMembers::className(), ['group_id' => 'id'])
            ->andOnCondition(['group_members.status' => 1, 'group_members.added_by' => Yii::$app->user->id]);
    }
}
