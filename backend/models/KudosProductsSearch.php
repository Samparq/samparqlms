<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\KudosProducts;

/**
 * KudosProductsSearch represents the model behind the search form about `backend\models\KudosProducts`.
 */
class KudosProductsSearch extends KudosProducts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'kpc_id', 'point', 'quantity', 'value', 'created_by', 'updated_by'], 'integer'],
            [['name', 'image', 'status', 'description', 'stock_status', 'created_at', 'updated_at'], 'safe'],
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
        $query = KudosProducts::find();

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
            'kpc_id' => $this->kpc_id,
            'point' => $this->point,
            'quantity' => $this->quantity,
            'value' => $this->value,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'stock_status', $this->stock_status]);

        return $dataProvider;
    }
}
