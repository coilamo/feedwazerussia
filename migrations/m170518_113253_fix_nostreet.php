<?php

use yii\db\Migration;

class m170518_113253_fix_nostreet extends Migration
{
    public function up()
    {
        $this->alterColumn('feed', 'street', $this->string(256)->defaultValue('No street'));
        $this->update('feed', ['street' => 'No street'], ['or' , ['street' => null], ['street' => '']]);
    }

    public function down()
    {
        echo "m170518_113253_fix_nostreet cannot be reverted.\n";

        return false;
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
