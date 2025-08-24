<?php

use yii\db\Migration;

class m250823_092457_create_table_useraccess extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_access}}', [
            'id' => $this->primaryKey(),
            'owner_id' => $this->integer()->notNull(),
            'viewer_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'idx-user_access-owner_id',
            'user_access',
            'owner_id'
        );
        $this->addForeignKey(
            'fk-user_access-owner_id',
            'user_access',
            'owner_id',
            'user',
            'id',
            'CASCADE'
        );
        $this->createIndex(
            'idx-user_access-viewer_id',
            'user_access',
            'viewer_id'
        );
        $this->addForeignKey(
            'fk-user_access-viewer_id',
            'user_access',
            'viewer_id',
            'user',
            'id',
            'CASCADE'
        );
        $this->insert('{{%user_access}}', [
            'owner_id' => '1',
            'viewer_id' => '2',
        ]);
        $this->insert('{{%user_access}}', [
            'owner_id' => '1',
            'viewer_id' => '3',
        ]);
        $this->insert('{{%user_access}}', [
            'owner_id' => '2',
            'viewer_id' => '1',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250823_092457_create_table_useraccess cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250823_092457_create_table_useraccess cannot be reverted.\n";

        return false;
    }
    */
}
