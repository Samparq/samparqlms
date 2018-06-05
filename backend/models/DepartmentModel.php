<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 3/2/18
 * Time: 11:53 AM
 */

namespace backend\models;


use common\models\User;
use Yii;

class DepartmentModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'department';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['code','name', 'name', 'short_name','description','default'], 'string']
        ];
    }


    public function getUser(){
        return $this->hasMany(User::className(), ['department' => 'name']);
    }


}
