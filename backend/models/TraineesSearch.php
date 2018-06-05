<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 25/9/17
 * Time: 1:15 PM
 */


namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Trainees;

/**
 * TraineesSearch represents the model behind the search form about `backend\models\Trainees`.
 */
class TraineesSearch extends Trainees
{
    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['id', 'training_id', 'user_id', 'status', 'created_by'], 'integer'],
            [['username', 'created_at'], 'safe'],
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
    public function search($params,$tid)
    {
        $query = Trainees::find()->where(['training_id' => $tid]);

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
            'training_id' => $this->training_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
