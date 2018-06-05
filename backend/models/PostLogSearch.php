<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/9/17
 * Time: 12:15 PM
 */


namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\PostLog;

/**
 * PostLogSearch represents the model behind the search form about `backend\models\PostLog`.
 */
class PostLogSearch extends PostLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'userid', 'post_id', 'comment_id'], 'integer'],
            [['username', 'created_at', 'created_by','description'], 'safe'],
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
        $query = PostLog::find()->orderBy('id DESC');

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
            'userid' => $this->userid,
            'post_id' => $this->post_id,
            'comment_id' => $this->comment_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
}