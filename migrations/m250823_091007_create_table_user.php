<?php

use yii\db\Migration;

class m250823_091007_create_table_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'password' => $this->string()->notNull(),
        ]);
        $this->insert('{{%user}}', [
            'name' => 'firstuser',
            'password' => '$2y$10$tT1etFZ9ZVy6gLGFpXYmROpHxf6wIpshBd2Tn/iv8juhidpua/bmS',
        ]);
        $this->insert('{{%user}}', [
            'name' => 'secuser',
            'password' => '$2y$10$pOhK1OyjuPKXm6kGx2y7mOW.x5dhRjWg4x6kI5QYXszM10hoHRpd6',
        ]);
        $this->insert('{{%user}}', [
            'name' => 'thirtuser',
            'password' => '$2y$10$zr7Cf0w/COdN/JoQglciiO0D0EpJknxjT.140vSKNWJ017rMVnwlS',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250823_091007_create_table_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250823_091007_create_table_user cannot be reverted.\n";

        return false;
    }
    */
}
