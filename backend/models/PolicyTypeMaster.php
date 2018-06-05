<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "policy_type_master".
 *
 * @property integer $id
 * @property string $name
 * @property string $flag
 */
class PolicyTypeMaster extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_type_master';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'flag'], 'string', 'max' => 45],
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
            'flag' => 'Flag',
        ];
    }
}
