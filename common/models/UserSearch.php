<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 9:23 AM
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */

    public  $utype;

    public function rules()
    {
        return [
            [['id', 'status', 'mobile_permission', 'mobile_no', 'key', 'update_status', 'form_submit', 'email_conf', 'super_admin'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'created_at', 'updated_at', 'name', 'last_name', 'flag', 'imei_app', 'app_regid', 'employee_id', 'dob', 'image_path', 'image_name','remark', 'lastlogin_time', 'test', 'auth_token','user_type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */

    public function convertUserType($type){

        if(empty($type)){
            return null;
        }

        $strLower = strtolower($type);

        if('pmo' == $strLower ) {
            return 1;
        } elseif('it'== $strLower) {
            return 2;
        } elseif('hr'== $strLower) {
            return 3;
        } elseif('finance' == $strLower) {
            return 4;
        } elseif('employee' == $strLower) {
            return 5;
        } else {

            return 123645;
        }
    }


    public function search($params,$limit)
    {

        $query = User::find()->where(['!=', 'id', 13])->orderBy('id DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'mobile_permission' => $this->mobile_permission,
            'dob' => $this->dob,
            'mobile_no' => $this->mobile_no,
            'key' => $this->key,
            'update_status' => $this->update_status,
            'form_submit' => $this->form_submit,
            'email_conf' => $this->email_conf,
            'super_admin' => $this->super_admin,
            'lastlogin_time' => $this->lastlogin_time,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'flag', $this->flag])
            ->andFilterWhere(['like', 'imei_app', $this->imei_app])
            ->andFilterWhere(['like', 'app_regid', $this->app_regid])
            ->andFilterWhere(['like', 'employee_id', $this->employee_id])
            ->andFilterWhere(['like', 'image_path', $this->image_path])
            ->andFilterWhere(['like', 'image_name', $this->image_name])
            ->andFilterWhere(['like', 'test', $this->test])
            ->andFilterWhere(['like', 'auth_token', $this->auth_token])
            ->andFilterWhere(['like', 'user_type', $this->convertUserType($this->user_type)]);

        return $dataProvider;
    }
}