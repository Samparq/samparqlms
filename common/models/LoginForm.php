<?php
namespace common\models;

use backend\models\Client;
use backend\models\LoginAttempts;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $client_code;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */

    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }

    public function rules()
    {
        return [

            [['username', 'password', 'client_code'], 'required'],

            ['rememberMe', 'boolean'],

            ['password', 'validatePassword'],
            ['password', 'checkPermission']
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $loginAttempt = new LoginAttempts();
                $loginAttempt->ipaddress = $_SERVER['REMOTE_ADDR'];
                $loginAttempt->username = $this->username;
                $loginAttempt->password = $this->password;
                $loginAttempt->save(false);

                $this->addError($attribute, 'Incorrect username, password or client code.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 30 : 0);
        } else {
            return false;
        }
    }

    public function checkPermission($attribute, $params){
        if(!empty($this->_user)){
            $clientModel = Client::findOne(['code' => $this->_user->client_code]);
            $currentTimeStamp = strtotime(date('Y-m-d H:i:s'));
            $subscriptionStamp = strtotime($clientModel->subscription_ed);
            if($currentTimeStamp>=$subscriptionStamp){
                return $this->addError($attribute,'Subscription has been expired, Please contact to smparqlms@qdegrees.com');
            }

        }
    }



    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            try {
                $this->_user = User::findByUsername($this->username);
            } catch (\Exception $ex){
                return;
            }
        }


        return $this->_user;
    }
}
