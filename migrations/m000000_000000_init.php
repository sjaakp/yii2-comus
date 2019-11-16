<?php

namespace sjaakp\comus\migrations;

use yii\db\Migration;

/**
 * Class m000000_000000_init
 * @package sjaakp\comus
 */
class m000000_000000_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%comment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'subject' => $this->string(80)->notNull(),
            'parent' => $this->integer()->unsigned()->null(),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'body' => $this->text()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
        ], $tableOptions);

        $this->createIndex('subject', '{{%comment}}', 'subject');
        $this->createIndex('parent', '{{%comment}}', 'parent');
        $this->createIndex('status', '{{%comment}}', 'status');
    }

    public function down()
    {
        $this->dropTable('{{%comment}}');
    }
}
