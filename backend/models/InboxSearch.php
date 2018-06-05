<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Inbox;

/**
 * InboxSearch represents the model behind the search form about `backend\models\Inbox`.
 */
class InboxSearch extends Inbox
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sent_id', 'mail_to', 'mail_from', 'flag', 'file_status', 'created_by', 'mail_to_userid'], 'integer'],
            [['subject', 'message', 'process_date', 'updated_on'], 'safe'],
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
    public function search($params,$criteria = false)
    {

        if($criteria === 'userview'){
            $query = Inbox::find()->where(['mail_to' => Yii::$app->user->identity->getId()]);
        } else {
            $query = Inbox::find();
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
            'sent_id' => $this->sent_id,
            'mail_to' => $this->mail_to,
            'mail_from' => $this->mail_from,
            'flag' => $this->flag,
            'file_status' => $this->file_status,
            'process_date' => $this->process_date,
            'created_by' => $this->created_by,
            'updated_on' => $this->updated_on,
            'mail_to_userid' => $this->mail_to_userid,
        ]);

        $query->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
