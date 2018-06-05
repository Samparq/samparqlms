<?php

namespace backend\models;

use common\models\User;
use Yii;

/**
 * This is the model class for table "training_submission".
 *
 * @property integer $id
 * @property integer $training_id
 * @property integer $question_id
 * @property integer $option_id
 * @property integer $training_submitted_by
 * @property string $created_at
 */
class TrainingSubmission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public $current;

    public static function tableName()
    {
        return 'training_submission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['training_id', 'question_id', 'option_id', 'training_submitted_by'], 'integer'],
            [['created_at','current','comment_box'], 'safe'],
            ['option_id','required', 'message' => 'Answer cannot be blank'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'training_id' => 'Training ID',
            'question_id' => 'Question ID',
            'option_id' => 'Option ID',
            'training_submitted_by' => 'Training Submitted By',
            'created_at' => 'Created At',
        ];
    }

    public function getUserDetails(){
        return $this->hasOne(User::className(), ['id' => 'training_submitted_by']);
    }

    public function getTrainingDetails(){
        return $this->hasOne(Training::className(), ['id' => 'training_id']);
    }

    public function getTrainingQuestions(){
        return $this->hasOne(TrainingQuestion::className(), ['id' => 'question_id']);
    }

    public function getTrainingOptions(){
        return $this->hasOne(Options::className(), ['id' => 'option_id']);
    }
}
