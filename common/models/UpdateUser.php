<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 12/9/17
 * Time: 5:52 PM
 */

namespace common\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class UpdateUser extends Model
{
    public $name;
    public $mobile_no;
    public $key;
    public $super_admin;
    public $right_status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','mobile_no','key','super_admin','right_status'], 'safe'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function update($id)
    {
        $user = \common\models\User::findOne($id);
        $user->name = $this->name;
        $user->mobile_no = $this->mobile_no;
        $user->key = $this->key;
        $user->super_admin = $this->super_admin;

        return $user->save() ? $user : null;
    }
}
