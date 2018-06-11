<?php

namespace backend\models;

use common\models\User;
use Yii;

/**
 * This is the model class for table "training".
 *
 * @property integer $id
 * @property string $trainer_name
 * @property string $training_title
 * @property string $start_date
 * @property string $end_date
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property string $file_original_name
 * @property string $file_new_name
 * @property string $welcome_template
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $status
 * @property integer $web_status
 * @property integer $duration
 * @property integer $enable_otp
 * @property integer $allow_prev
 * @property integer $allow_print_answersheet
 * @property integer $show_answersheet
 * @property integer $show_result
 * @property string $thanks_template
 */
class Training extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function getDb(){
        return Yii::$app->get('dbDynamic');
    }

    public static function tableName()
    {
        return 'training';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['welcome_template','web_status','enable_otp','allow_prev','allow_print_answersheet','show_answersheet','instructions','thanks_template','created_at', 'updated_at','status','file_original_name','youtube_url','training_question_status','shuffle_question','feedback_required'], 'safe'],
            [['created_by', 'updated_by','download_report','assessment_type','show_result','training_type','duration','availability_status','pass_score'], 'integer'],
            [['file_new_name'], 'file', 'extensions' => 'jpg,png'],
            [['start_date', 'end_date','description','training_sd','training_ed','trainer_name','training_title','training_type','duration'], 'required', 'message' => 'Field cannot be blank'],
            [['trainer_name','client_code'], 'string', 'max' => 100],
            [['training_title','feedback_message'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 250],
            ['end_date', 'validateEndDate', 'params' => ['startDate' => 'start_date']],
            ['start_date', 'validateStartDate'],
            ['training_sd', 'validateAssessmentSD', 'params' => ['training_ed','start_date','end_date']],
            ['training_ed', 'validateAssessmentED', 'params' => ['end_date','training_sd']],
            ['duration', 'validateDuration','params' => ['training_ed','training_sd']]
        ];
    }

    public function validateEndDate($attribute, $params){
        $endDate= $this->$attribute;
        $startDate= $this->$params['startDate'];
        if($endDate <= $startDate){
            $this->addError($attribute, 'End date must be greater than start date');
        }
    }


    public function validateStartDate($attribute){
        $startDate = $this->$attribute;
        $currentDateTime = date("Y-m-d H:i");
        if($startDate<$currentDateTime){
            $this->addError($attribute, 'Start date must me greater than current date');
        }
    }

    public function validateAssessmentSD($attribute, $params){
        $assessmentDate = $this->$attribute;
        $trainingEd = $this->$params[0];
        $startDate = $this->$params[1];
        $endDate = $this->$params[2];
        if(empty($startDate) || empty($endDate)){
            $this->addError($attribute, 'Please select start date and end date first');
        } else

        if($assessmentDate <= $startDate || $assessmentDate >= $endDate){
            $this->addError($attribute, 'Assessment start date must be greater than start date and less than end date');
        }
    }

    public function validateDuration($attribute, $params){
        $duration = $this->$attribute;
        $trainingEd = $this->$params[0];
        $trainingSd = $this->$params[1];
        $diff = round((strtotime($trainingEd) - strtotime($trainingSd))/60, 2);
        if($duration > $diff){
            $this->addError($attribute, 'Max minutes must be less than '.$diff);
        }
    }


    public function validateAssessmentED($attribute,$params){
        $trainingED = $this->$attribute;
        $endDate = $this->$params[0];
        $trainingSD = $this->$params[1];
        if($trainingED < $trainingSD || $trainingED > $endDate){
            $this->addError($attribute, 'Assessment end date must be less than end date and greater than assessment start date');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trainer_name' => 'Trainer Name',
            'training_title' => 'Training Title',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'file_new_name' => 'Upload Picture',
            'youtube_url' => 'Youtube Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getMaterials(){
        return $this->hasMany(TrainingMaterial::className(), ['training_id' => 'id']);
    }

    public function getTrainees(){
        return $this->hasMany(Trainees::className(), ['training_id' => 'id']);
    }

    public function getQuestions(){
        return $this->hasMany(TrainingQuestion::className(), ['training_id' => 'id']);
    }

    public function getSubmissions(){
        return $this->hasMany(TrainingSubmission::className(), ['training_id' => 'id']);
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['email' => 'trainer_name']);
    }
}
