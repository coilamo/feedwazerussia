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
            ['polyline', 'validatePolyline'],
            ['type', 'validateType'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/feed', 'ID'),
            'incident_id' => Yii::t('app/feed', 'Incident ID'),
            'description' => Yii::t('app/feed', 'Description'),
            'created_at' => Yii::t('app/feed', 'Created At'),
            'updated_at' => Yii::t('app/feed', 'Updated At'),
            'incident' => Yii::t('app/feed', 'Incident'),
            'incidents' => Yii::t('app/feed', 'Incidents'),
            'location' => Yii::t('app/feed', 'Location'),
            'polyline' => Yii::t('app/feed', 'Polyline'),
            'starttime' => Yii::t('app/feed', 'Start time'),
            'endtime' => Yii::t('app/feed', 'End time'),
            'street' => Yii::t('app/feed', 'Street'),
            'type' => Yii::t('app/feed', 'Type'),
            'direction' => Yii::t('app/feed', 'Direction'),
            'author_id' => Yii::t('app/feed', 'Author ID'),
            'reference' => Yii::t('app/feed', 'Reference'),
            'source' => Yii::t('app/feed', 'Source'),
            'location_description' => Yii::t('app/feed', 'Location Description'),
            'name' => Yii::t('app/feed', 'Name'),
            'parent_event' => Yii::t('app/feed', 'Parent Event'),
            'schedule' => Yii::t('app/feed', 'Schedule'),
            'short_description' => Yii::t('app/feed', 'Short Description'),
            'subtype' => Yii::t('app/feed', 'Subtype'),
            'url' => Yii::t('app/feed', 'Url'),
            'active' => Yii::t('app/feed', 'Active'),
            'mail_send' => Yii::t('app/feed', 'Mail Send'),
            'comment' => Yii::t('app/feed', 'Comment'),
            'authorFilterInput' => Yii::t('app/feed', 'Author'),
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
    
    public function validatePolyline($attribute, $params, $validator)
    {
        $polyline = explode(' ', $this->$attribute);
        if (count($polyline) < 2 || count($polyline) % 2 != 0)
        {
            $this->addError($attribute, Yii::t('app/feed', 'Polyline is incorrectly formatted.'));
        }
        
        if (count($polyline) > 3)
        {
            $lat1 = $polyline[0];
            $lon1 = $polyline[1];
            $lat2 = $polyline[count($polyline) - 2];
            $lon2 = $polyline[count($polyline) - 1];
            $distance = Feed::haversineGreatCircleDistance($lat1, $lon1, $lat2, $lon2);
            if ($distance < 40)
            {
                $this->addError($attribute,
                        Yii::t('app/feed',
                        'Distance between points should be more than 40 meters (Currently {distance, plural, =0{are # meters} =1{is 1 meter} other{are # meters}}!).',
                                ['distance' => $distance]));
            }
        }
    }

    public function validateType($attribute, $params, $validator)
    {
        /*if ($this->author->country == 2) { // Belarus
            if ($this->$attribute == 'CHIT_CHAT') {
                $this->addError($attribute, Yii::t('app/feed', 'Chat category is disabled for your country.'));
            }
        }*/
    }
    
    private static function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
          cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
