<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\KudosPoints;

/**
 * KudosPointsSearch represents the model behind the search form about `backend\models\KudosPoints`.
 */
class KudosPointsSearch extends KudosPoints
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'kudos_id', 'created_by', 'updated_by'], 'integer'],
            [['point', 'earned_date', 'expiry_date', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $reqType, $userid = false)
    {

        if($reqType == 'user'){
            $query = KudosPoints::find()->groupBy('user_id');
        } else {
            $query = KudosPoints::find()
                ->joinWith('kudosDetails')
                ->where(['user_id' => $userid]);
        }

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
            'earned_date' => $this->earned_date,
            'expiry_date' => $this->expiry_date,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'point', $this->point]);

        return $dataProvider;
    }
}
