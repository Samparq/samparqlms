<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 5/9/17
 * Time: 10:10 AM
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Sent;

/**
 * SentSearch represents the model behind the search form about `backend\models\Sent`.
 */
class SentSearch extends Sent
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'mail_from', 'flag', 'status', 'file_status'], 'integer'],
            [['mail_to', 'to_detail', 'process_date', 'subject', 'message'], 'safe'],
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
    public function search($params, $self = false)
    {
        $query = Sent::find()->where(['mail_from' => Yii::$app->user->id])->orderBy('id DESC');

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
            'mail_from' => $this->mail_from,
            'flag' => $this->flag,
            'process_date' => $this->process_date,
            'status' => $this->status,
            'file_status' => $this->file_status,
        ]);

        $query->andFilterWhere(['like', 'mail_to', $this->mail_to])
            ->andFilterWhere(['like', 'to_detail', $this->to_detail])
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}