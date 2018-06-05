<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\LoginLog;

/**
 * LoginLogSearch represents the model behind the search form about `backend\models\LoginLog`.
 */
class LoginLogSearch extends LoginLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'userid'], 'integer'],
            [['username', 'login_time','last_activity', 'logout_time', 'total_time', 'created_at', 'created_by'], 'safe'],
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
        $query = LoginLog::find()->orderBy('id DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'userid' => $this->userid,
            'login_time' => $this->login_time,
            'logout_time' => $this->logout_time,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'total_time', $this->total_time])
            ->andFilterWhere(['like', 'created_by', $this->created_by])
            ->andFilterWhere([
            'like',
            'FROM_UNIXTIME(logout_time, "%Y-%m-%d")',
            $this->logout_time
        ])
            ->andFilterWhere([
                'like',
                'FROM_UNIXTIME(logout_time, "%h:%i:%s %p")',
                $this->logout_time
            ]);


        return $dataProvider;
    }
}
