<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 28/5/18
 * Time: 1:33 PM
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Client;

/**
 * ClientSearch represents the model behind the search form about `backend\models\Client`.
 */
class ClientSearch extends Client
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'license_id', 'server_id', 'no_of_users', 'created_by'], 'integer'],
            [['name', 'code', 'subscription_sd', 'subscription_ed', 'status', 'created_at'], 'safe'],
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
        $query = Client::find();

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
            'license_id' => $this->license_id,
            'server_id' => $this->server_id,
            'no_of_users' => $this->no_of_users,
            'subscription_sd' => $this->subscription_sd,
            'subscription_ed' => $this->subscription_ed,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}

