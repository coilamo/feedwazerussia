<?php

use yii\db\Migration;

/**
 * Class m180903_220653_add_county_to_user
 */
class m180903_220653_add_county_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'country', $this->integer());
        $this->update('user', ['country' => 1], ['country' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'country');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180903_220653_add_county_to_user cannot be reverted.\n";

        return false;
    }
    */
}
