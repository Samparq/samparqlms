<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 24/4/18
 * Time: 4:49 PM
 */


namespace backend\models;

use Yii;

/**
 * This is the model class for table "question_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $status
 * @property string $created_at
 * @property integer $created_by
 */
class QuestionType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */




    public static function tableName()
    {
        return 'question_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','status','type','comment'], 'safe']
        ];
    }

}

