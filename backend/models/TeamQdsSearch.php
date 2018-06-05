<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\TeamQds;

/**
 * TeamQdsSearch represents the model behind the search form about `backend\models\TeamQds`.
 */
class TeamQdsSearch extends TeamQds
{
    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
                [['email'], 'string'],
            ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {

        return Model::scenarios();
    }


    public function search($params)
    {
        $query = TeamQds::find()->orderBy('id DESC');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
