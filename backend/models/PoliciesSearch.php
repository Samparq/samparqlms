<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Policies;

/**
 * PoliciesSearch represents the model behind the search form about `backend\models\Policies`.
 */
class PoliciesSearch extends Policies
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'profile_id', 'flag', 'policy_type'], 'integer'],
            [['file_name', 'orignal_filename', 'file_path', 'ext', 'file_size', 'process_date', 'description', 'title'], 'safe'],
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
        $query = Policies::find()->orderBy('id DESC');

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
            'created_by' => $this->created_by,
            'process_date' => $this->process_date,
            'profile_id' => $this->profile_id,
            'flag' => $this->flag,
            'policy_type' => $this->policy_type,
        ]);

        $query->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'orignal_filename', $this->orignal_filename])
            ->andFilterWhere(['like', 'file_path', $this->file_path])
            ->andFilterWhere(['like', 'ext', $this->ext])
            ->andFilterWhere(['like', 'file_size', $this->file_size])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
