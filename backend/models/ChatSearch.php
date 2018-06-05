<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 23/5/18
 * Time: 11:03 AM
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Chat;

/**
 * ChatSearch represents the model behind the search form about `backend\models\Chat`.
 */
class ChatSearch extends Chat
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'training_id', 'sender_id', 'receiver_id', 'read_status', 'status', 'attachment_status', 'attachment_type'], 'integer'],
            [['message', 'original_filename', 'new_filename', 'file_path', 'created_at'], 'safe'],
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
        $query = Chat::find()->orderBy(['id' => SORT_DESC])->groupBy(['training_id','sender_id']);

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
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'read_status' => $this->read_status,
            'status' => $this->status,
            'attachment_status' => $this->attachment_status,
            'attachment_type' => $this->attachment_type,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'original_filename', $this->original_filename])
            ->andFilterWhere(['like', 'new_filename', $this->new_filename])
            ->andFilterWhere(['like', 'file_path', $this->file_path]);

        return $dataProvider;
    }
}
