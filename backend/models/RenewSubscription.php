<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 7/6/18
 * Time: 10:31 AM
 */
 namespace backend\models;


use yii\base\Model;

class RenewSubscription extends Model {

    public $no_of_months;
    public $no_of_users;


    public function rules (){
        return [
            [['no_of_months','no_of_users'], 'required', 'message' => 'Field can\'t be blank'],
            [['no_of_months','no_of_users'], 'integer'],
        ];
    }

}