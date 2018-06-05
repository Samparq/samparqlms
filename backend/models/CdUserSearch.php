<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 9:23 AM
 */

namespace backend\models;

use backend\models\CdUser;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class CdUserSearch extends CdUser
{
    /**
     * @inheritdoc
     */



    public function rules()
    {
        return [
            [['id','active'], 'integer'],
            [['employee_id','aadhar_no','name','phone','marital_status','branch','department','designation','reporting_in_charge1','reporting_in_charge2','category','gender','pan','email'], 'string'],
            [['joining_date','confirmation_date','dob'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }





    public function search($params,$limit)
    {

        $query = CdUser::find()->orderBy('id DESC');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }



        $query->andFilterWhere(['like', 'employee_id', $this->employee_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'aadhar_no', $this->aadhar_no])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'active', $this->active])
            ->andFilterWhere(['like', 'dob', $this->dob])
            ->andFilterWhere(['like', 'joining_date', $this->joining_date])
            ->andFilterWhere(['like', 'confirmation_date', $this->confirmation_date])
            ->andFilterWhere(['like', 'marital_status', $this->marital_status])
            ->andFilterWhere(['like', 'branch', $this->branch])
            ->andFilterWhere(['like', 'department', $this->department])
            ->andFilterWhere(['like', 'designation', $this->designation])
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'reporting_in_charge1', $this->reporting_in_charge1])
            ->andFilterWhere(['like', 'reporting_in_charge2', $this->reporting_in_charge2]);

        return $dataProvider;
    }
}