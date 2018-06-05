<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\TblFeedback;

/**
 * TblFeedbackSearch represents the model behind the search form about `backend\models\TblFeedback`.
 */
class TblFeedbackSearch extends TblFeedback
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sender_id'], 'integer'],
            [['sender_name', 'feedback_str', 'date_time'], 'safe'],
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
        $query = TblFeedback::find();

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
            'sender_id' => $this->sender_id,
            'date_time' => $this->date_time,
        ]);

        $query->andFilterWhere(['like', 'sender_name', $this->sender_name])
            ->andFilterWhere(['like', 'feedback_str', $this->feedback_str]);

        return $dataProvider;
    }
}
