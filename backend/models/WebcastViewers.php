<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "webcast_viewers".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $webcast_id
 * @property integer $view_status
 * @property string $start_date
 * @property string $end_date
 * @property integer $total
 */
class WebcastViewers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webcast_viewers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'webcast_id', 'view_status', 'total'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'webcast_id' => 'Webcast ID',
            'view_status' => 'View Status',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'total' => 'Total',
        ];
    }
}
