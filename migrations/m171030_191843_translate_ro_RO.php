<?php

use yii\db\Migration;

class m171030_191843_translate_ro_RO extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('lang', ['url', 'local', 'name', 'default', 'date_update', 'date_create'], [
            ['ro', 'ro-RO', 'Romanian', 0, time(), time()],
        ]);
    }

    public function safeDown()
    {
        echo "m171030_191843_translate_ro_RO cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171030_191843_translate_ro_RO cannot be reverted.\n";

        return false;
    }
    */
}
