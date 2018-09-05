<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Feed;

/**
 * FeedSearch represents the model behind the search form about `app\models\Feed`.
 */
class FeedSearch extends Feed
{
    public $authorFilterInput;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'author_id', 'active', 'mail_send'], 'integer'],
            [['incident_id', 'description', 'incident', 'incidents', 'location', 'polyline', 'starttime', 'endtime',
                'street', 'type', 'direction', 'reference', 'source', 'location_description', 'name', 'parent_event',
                'schedule', 'short_description', 'subtype', 'url', 'comment', 'authorFilterInput'], 'safe'],
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
    public function search($params)
    {
        $query = Feed::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['authorFilterInput'] = [
            'asc' => ['user.login' => SORT_ASC],
            'desc' => ['user.login' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions

        $query->joinWith(['author']);//leftJoin('user', '`user`.`id` = `feed`.`author_id`');
        if (!\Yii::$app->user->isGuest && \Yii::$app->user->identity->country == 2) {
            $query->andFilterWhere(['user.country' => 2]);
        } else {
            $query->andFilterWhere(['author_id' => \Yii::$app->user->getId()]);
        }

        $query->andFilterWhere(['like', 'feed.id', $this->id])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'incident', $this->incident])
            ->andFilterWhere(['like', 'incidents', $this->incidents])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'polyline', $this->polyline])
            ->andFilterWhere(['like', 'street', $this->street])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'direction', $this->direction])
            ->andFilterWhere(['like', 'reference', $this->reference])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'location_description', $this->location_description])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'parent_event', $this->parent_event])
            ->andFilterWhere(['like', 'schedule', $this->schedule])
            ->andFilterWhere(['like', 'short_description', $this->short_description])
            ->andFilterWhere(['like', 'subtype', $this->subtype])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'user.login', $this->authorFilterInput]);

        return $dataProvider;
    }
}
