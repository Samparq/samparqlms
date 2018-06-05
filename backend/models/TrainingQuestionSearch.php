<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 16/10/17
 * Time: 9:58 AM
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\TrainingQuestion;

/**
 * TrainingQuestionSearch represents the model behind the search form about `backend\models\TrainingQuestion`.
 */
class TrainingQuestionSearch extends TrainingQuestion
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'training_id', 'type', 'has_negative', 'negative_mark', 'status', 'created_by', 'updated_by'], 'integer'],
            [['question', 'created_at', 'updated_at'], 'safe'],
            [['marks'], 'number'],
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
    public function search($params,$tid,$uid = false)
    {

        if(!empty($uid)){

            $query = TrainingQuestion::find()->where(['created_by' => $uid]);

        } else {

            $query = TrainingQuestion::find()->where(['training_id' => $tid]);

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
            'training_id' => $this->training_id,
            'type' => $this->type,
            'marks' => $this->marks,
            'has_negative' => $this->has_negative,
            'negative_mark' => $this->negative_mark,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'question', $this->question]);

        return $dataProvider;
    }
}