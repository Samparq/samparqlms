<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "training_question".
 *
 * @property integer $id
 * @property integer $training_id
 * @property string $question
 * @property integer $option_id
 * @property integer $type
 * @property integer $ref_id
 * @property double $marks
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $negative_mark
 * @property integer $has_negative
 */
class TrainingQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public $is_submitted;
    public $import_id;

    public static function tableName()
    {
        return 'training_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['training_id', 'type', 'status', 'created_by', 'is_required', 'updated_by', 'ref_id'], 'integer'],
            [['training_id', 'type', 'question', 'import_id'], 'required', 'message' => 'Field can\'t be blank'],
            [['question'], 'string'],
            [['marks'], 'number', 'min' => 1],
            [['created_at', 'negative_mark', 'updated_at', 'is_submitted', 'has_negative','image'], 'safe'],
            [['negative_mark'], 'validateMarks', 'params' => ['marks' => 'marks', 'type' => 'type', 'has_negative' => 'has_negative']],
            [['marks'], 'validateMarksReq', 'params' => ['type' => 'type']],
            [['import_id'], 'validateImporter', 'params' => ['training_id' => 'training_id', 'type' => 'type']],
        ];
    }

    public function validateImporter($attribute, $params)
    {
        $checkIfRecordExist = TrainingQuestion::find()
            ->where(['training_id' => $this->$attribute, 'ref_id' => $this->$params['training_id'], 'status' => 1])
            ->count();



        if (!empty($this->$params['training_id'])) {
            $trainingModel = Training::findOne(['id' => $this->$attribute]);

            if(Yii::$app->samparq->checkDisability($trainingModel->end_date, false) == true){
                $this->addError($attribute, "Training has been expired");
            } else if($trainingModel->availability_status != 0){

                $this->addError($attribute, "Questions can not be added because training is published");

            } else {
                $checkQuestions = TrainingQuestion::find()
                    ->where(['training_id' => $this->$params['training_id'], 'status' => 1])
                    ->count();

                if (!($checkQuestions > 0)) {
                    $this->addError($attribute, "Training you have selected has no questions");
                }


                if ($checkIfRecordExist > 0) {
                    $this->addError($attribute, "Questions for this training has been already imported");
                }
            }

        }



    }

    public function validateMarks($attribute, $params)
    {
        $marks = $this->$attribute;

        $max_marks = $this->$params["marks"];
        $type = $this->$params["type"];
        $is_negative = $this->$params["has_negative"];

        if ($marks > $max_marks && $is_negative == 1 && $type != 2) {
            $this->addError($attribute, "Negative marking cannot exceed above marks (i.e $max_marks)");
        }
    }

    public function validateMarksReq($attribute, $params)
    {
        $type = $this->$params["type"];
        if ($type != 2) {
            $this->addError($attribute, "Marks cannot be empty");
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'training_id' => 'Training ID',
            'question' => 'Enter your question',
            'option_id' => 'Option ID',
            'type' => 'Question Type',
            'has_negative' => 'Does question has negative marking?',
            'negative_mark' => 'Negative Marks',
            'is_required' => 'Mark as mandatory',
            'marks' => 'Marks',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function getOptions()
    {
        return $this->hasMany(Options::className(), ['tquestion_id' => 'id']);
    }
}
