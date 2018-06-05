<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\TblPost;

/**
 * TblPostSearch represents the model behind the search form about `backend\models\TblPost`.
 */
class TblPostSearch extends TblPost
{
    /**
     * @inheritdoc
     */

    public $c_attach_status;

    public function rules()
    {
        return [
            [['id', 'post_userid', 'attach_status', 'remove_id'], 'integer'],
            [['post_sendername','c_attach_status','post_status', 'post_description', 'post_datetime'], 'safe'],
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



    public function convertAttachStatus($attStatus){

        if(empty($attStatus)){
            return null;
        }

        $strLower = strtolower($attStatus);

        if($strLower === 'yes') {
            return 1;
        } elseif($strLower === 'no') {
            return 0;
        } else {
            return 123645;
        }
    }


    public function convertPostStatus($attStatus){

        if(empty($attStatus)){
            return null;
        }

        $strLower = strtolower($attStatus);

        if($strLower == 'delete') {
            return 2;
        } elseif($strLower == 'active') {
            return 1;
        } elseif($strLower == 'inactive') {
            return 0;
        } else {
            return 123645;
        }
    }


    public function search($params)
    {
        $query = TblPost::find()->orderBy('id DESC');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'post_userid' => $this->post_userid,
            'post_datetime' => $this->post_datetime,
            'post_status' => $this->convertPostStatus($this->post_status),
            'remove_id' => $this->remove_id,
        ]);

        $query->andFilterWhere(['like', 'post_sendername', $this->post_sendername])
            ->andFilterWhere(['like', 'post_description', $this->post_description])
            ->andFilterWhere(['like', 'attach_status', $this->convertAttachStatus($this->c_attach_status)]);

        return $dataProvider;
    }
}
