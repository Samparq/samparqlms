<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\KudosRedemption;

/**
 * KudosRedemptionSearch represents the model behind the search form about `backend\models\KudosRedemption`.
 */
class KudosRedemptionSearch extends KudosRedemption
{
    /**
     * @inheritdoc
     *
     */

    public $product_price;
    public $product_name;
    public $kudos_type;


    public function rules()
    {
        return [
            [['id', 'user_id', 'kudos_id', 'kudos_product_id'], 'integer'],
            [['redemption_date','product_name', 'product_price','kudos_type'], 'safe'],
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
    public function search($params,$userid)
    {
        $query = KudosRedemption::find()
            ->joinWith(['productDetails','kudosDetails'])
            ->where(['user_id' => $userid]);
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
            'user_id' => $this->user_id,
            'kudos_id' => $this->kudos_id,
            'kudos_product_id' => $this->kudos_product_id,
            'redemption_date' => $this->redemption_date,
            'product_price' => $this->product_price,
            'product_name' => $this->product_name,
            'kudos_type' => $this->kudos_type,
        ]);

        return $dataProvider;
    }
}
