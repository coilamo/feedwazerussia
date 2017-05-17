<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "feed".
 *
 * @property integer $id
 * @property string $incident_id
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $incident
 * @property string $incidents
 * @property string $location
 * @property string $polyline
 * @property string $starttime
 * @property string $endtime
 * @property string $street
 * @property string $type
 * @property string $direction
 * @property integer $author_id
 * @property string $reference
 * @property string $source
 * @property string $location_description
 * @property string $name
 * @property string $parent_event
 * @property string $schedule
 * @property string $short_description
 * @property string $subtype
 * @property string $url
 * @property integer $active
 * @property integer $mail_send
 * @property string $comment
 *
 * @property User $author
 */
class Feed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['incident_id', 'endtime', 'active', 'mail_send', 'comment', 'type', 'direction'], 'required'],
            [['created_at', 'updated_at', 'author_id', 'active', 'mail_send', 'created_at', 'updated_at'], 'integer'],
            [['starttime', 'endtime'], 'safe'],
            [['incident_id', 'incident', 'incidents'], 'string', 'max' => 32],
            [['type', 'direction'], 'string', 'min' => 1, 'max' => 32],
            [['description', 'location', 'polyline', 'street', 'reference', 'source', 'location_description', 'name', 'parent_event', 'schedule', 'short_description', 'subtype', 'url', 'comment'], 'string', 'max' => 256],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'incident_id' => Yii::t('app', 'Incident ID'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'incident' => Yii::t('app', 'Incident'),
            'incidents' => Yii::t('app', 'Incidents'),
            'location' => Yii::t('app', 'Location'),
            'polyline' => Yii::t('app', 'Polyline'),
            'starttime' => Yii::t('app', 'Start time'),
            'endtime' => Yii::t('app', 'End time'),
            'street' => Yii::t('app', 'Street'),
            'type' => Yii::t('app', 'Type'),
            'direction' => Yii::t('app', 'Direction'),
            'author_id' => Yii::t('app', 'Author ID'),
            'reference' => Yii::t('app', 'Reference'),
            'source' => Yii::t('app', 'Source'),
            'location_description' => Yii::t('app', 'Location Description'),
            'name' => Yii::t('app', 'Name'),
            'parent_event' => Yii::t('app', 'Parent Event'),
            'schedule' => Yii::t('app', 'Schedule'),
            'short_description' => Yii::t('app', 'Short Description'),
            'subtype' => Yii::t('app', 'Subtype'),
            'url' => Yii::t('app', 'Url'),
            'active' => Yii::t('app', 'Active'),
            'mail_send' => Yii::t('app', 'Mail Send'),
            'comment' => Yii::t('app', 'Comment'),
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
}