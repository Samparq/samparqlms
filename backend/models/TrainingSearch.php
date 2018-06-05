<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Training;


class TrainingSearch extends Training
{
    /**
     * @inheritdoc
     */

    public $date;
    public $totalQuestion;

    public function rules()
    {
        return [
            [['id', 'created_by','totalQuestion', 'updated_by'], 'integer'],
            [['trainer_name', 'status','date','training_title', 'start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
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

    public function getStartDate($date){
        print_r($date);
        die;
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
        if(Yii::$app->user->can('admin')){
            $query = Training::find()->orderBy('id DESC');
        } else if (Yii::$app->user->can('monitor')){
            $query = Training::find()
                ->where(['client_code' => Yii::$app->session->get('client')])
                ->orderBy('id DESC');
        } else {
            $query = Training::find()
                ->where(['created_by' => Yii::$app->user->id])
                ->orderBy('id DESC');
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

//        if(!empty($this->start_date) && strpos($this->start_date, '-') !== false) {
//            list($start_date, $end_date) = explode(' - ', $this->start_date);
//            $query->andFilterWhere(['between', 'start_date', strtotime($start_date), strtotime($end_date)]);
//        }

        $query->andFilterWhere(['like', 'trainer_name', $this->trainer_name])
            ->andFilterWhere(['like', 'training_title', $this->training_title]);

        if(!empty($this->start_date) && strpos($this->start_date, '-') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->start_date);
            $query->andFilterWhere(['between', 'start_date', $start_date, $end_date]);
        }

        if(!empty($this->end_date) && strpos($this->end_date, '-') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->end_date);
            $query->andFilterWhere(['between', 'end_date', $start_date, $end_date]);
        }


        return $dataProvider;
    }
}
