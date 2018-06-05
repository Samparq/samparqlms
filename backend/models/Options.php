<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "options".
 *
 * @property integer $id
 * @property integer $tquestion_id
 * @property string $option_value
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $is_answer
 */
class Options extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */


    public $max_marks;
    public $question_type;

    public static function tableName()
    {
        return 'training_options';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tquestion_id', 'created_by','is_answer','question_type', 'updated_by'], 'integer'],
            [['option_value'], 'string'],
            [['created_at', 'updated_at','max_marks'], 'safe'],
        ];
    }





    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tquestion_id' => 'Tquestion ID',
            'option_value' => 'Enter an answer choice',
            'is_answer' => 'Is this the correct answer?',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
