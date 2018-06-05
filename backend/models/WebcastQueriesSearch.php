<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 12/10/17
 * Time: 10:23 AM
 */


namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\WebcastQueries;

/**
 * WebcastQueryThreadSearch represents the model behind the search form about `backend\models\WebcastQueries`.
 */
class WebcastQueriesSearch extends WebcastQueries
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['webcast_id', 'regid','notification_status'], 'integer'],
            [['query_type','empid','created_time','name', 'location','query','email_id','phone'], 'safe']
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
        $query = WebcastQueries::find();

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
            'id' => $this->id
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}