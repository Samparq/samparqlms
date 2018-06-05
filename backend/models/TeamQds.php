<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 22/12/17
 * Time: 5:41 PM
 */


namespace backend\models;

use Yii;
use yii\validators\EmailValidator;

/**
 * This is the model class for table "team_qds".
 *
 * @property integer $id
 * @property string $email
 * @property string $created_on
 * @property integer $created_by
 */
class TeamQds extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'team_qds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_by'], 'integer'],
            [['email'], 'required'],
            [['email'], 'checkEmailList'],
            [['created_at'], 'safe']
        ];
    }

    public function checkEmailList($attribute, $params) {
        $validator = new EmailValidator();
        $emails = is_array($this->email)? : explode(',', $this->email);
        foreach ($emails as $email) {
            $validator->validate($email)? : $this->addError($attribute, $email." is not a valid email.");
        }
    }


}

