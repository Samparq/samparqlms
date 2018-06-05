<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 13/9/17
 * Time: 12:38 PM
 */


namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\LoginLog;

/**
 * LoginLogSearch represents the model behind the search form about `backend\models\LoginLog`.
 */
class LoginAttemptsSearch extends LoginAttempts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'ipaddress', 'username','status','attempt_time', 'created_at', 'created_by'], 'safe'],
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
    public function search($params)
    {
        $query = LoginAttempts::find()->orderBy('id DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }


        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'ipaddress', $this->ipaddress])
            ->andFilterWhere(['like', 'attempt_time', $this->attempt_time])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'created_by', $this->created_by])
            ->andFilterWhere(['like', 'password', $this->password]);



        return $dataProvider;
    }
}
