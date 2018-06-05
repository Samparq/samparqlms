<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 29/9/17
 * Time: 1:53 PM
 */


namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Trainees;

/**
 * AssessmentSearch represents the model behind the search form about `backend\models\Trainees`.
 */
class AssessmentSearch extends TrainingSubmission
{
    /**
     * @inheritdoc
     */

    public  $training_title;
    public  $global;
    public  $question;
    public  $trainer_name;
    public  $username;
    public  $total_marks;
    public  $marks_obtained;
    public  $overall_percentage;
    public  $assessment_type;

    public function rules()
    {
        return [
            [['id', 'training_id','overall_percentage', 'question_id','assessment_type', 'total_marks','marks_obtained','option_id', 'training_submitted_by'], 'integer'],
            [['training_title','username','trainer_name','question'], 'string']
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
    public function search($params,$tid =false)
    {

        if(isset($tid) && !empty($tid) && $tid != "all"){
            $query = TrainingSubmission::find()
                ->where(['training_id' => $tid])
                ->joinWith('userDetails')
                ->joinWith('userDetails')
                ->orderBy(['training_submission.id' => SORT_DESC])
                ->select('training_submitted_by,training_id')
                ->distinct();
        } else {
            $query = TrainingSubmission::find()
                ->joinWith('userDetails')
                ->joinWith('trainingDetails')
                ->orderBy(['training_submission.id' => SORT_DESC])
                ->select('training_submitted_by,training_id')
                ->distinct();

        }






        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,

        ]);



        $this->load($params);

        if (!$this->validate()) {

            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'training_id' => $this->training_id,
            'question_id' => $this->question_id,
            'option_id' => $this->option_id,
        ]);

        if(isset($params['global'])){
            $query->andFilterWhere(['like', 'user.name', $params['global']]);
            $query->orFilterWhere(['like', 'user.name', $params['global']]);
            $query->orFilterWhere(['like', 'training.training_title', $params['global']]);

        }

        return $dataProvider;
    }
}
