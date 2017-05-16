<?php

use yii\db\Migration;

class m170514_180344_feed extends Migration
{
    public function up()
    {
        $this->createTable('feed', [
            'id' => $this->primaryKey(),
            'incident_id' => $this->string(32)->notNull(),
            'description' => $this->string(256),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'incident' => $this->string(32),
            'incidents' => $this->string(32),
            'location' => $this->string(256),
            'polyline' => $this->string(256),
            'starttime' => $this->string(256)->notNull(),
            'endtime' => $this->string(256)->notNull(),
            'street' => $this->string(256),
            'type' => $this->string(32),
            'direction' => $this->string(32),
            'author_id' => $this->integer(),
            'reference' => $this->string(256),
            'source' => $this->string(256),
            'location_description' => $this->string(256),
            'name' => $this->string(256),
            'parent_event' => $this->string(256),
            'schedule' => $this->string(256),
            'short_description' => $this->string(256),
            'subtype' => $this->string(256),
            'url' => $this->string(256),
            'active' => $this->boolean()->notNull(),
            'mail_send' => $this->boolean()->notNull(),
            'comment' => $this->string(256)->notNull(),
        ]);
        
        $this->createIndex(
            'idx-feed-author_id',
            'feed',
            'author_id'
        );

        $this->addForeignKey(
            'fk-feed-author_id',
            'feed',
            'author_id',
            'user',
            'id',
            'NO ACTION');
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-feed-author_id',
            'feed'
        );

        $this->dropIndex(
            'idx-feed-author_id',
            'feed'
        );
        $this->dropTable('feed');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
