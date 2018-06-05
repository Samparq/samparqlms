<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/10/17
 * Time: 1:01 PM
 */

namespace console\models;

use console\models\AuthAssignment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class AuthAssignmentSearch extends AuthAssignment
{
    /**
     * @inheritdoc
     */

    public $name;

    public function rules()
    {
        return [
           [['user_id','item_name','name'], 'safe']
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
    public function search($params,$role =false)
    {

        if(isset($role) && !empty($role) && $role != "all"){
            $query = AuthAssignment::find()
                ->where(['item_name' => $role])->groupBy('user_id');
        } else {
            $query = AuthAssignment::find()->groupBy('user_id');
        }

        $query->joinWith('user');




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

        $query->andFilterWhere(['like', 'user.name', $this->name]);


        return $dataProvider;
    }
}
