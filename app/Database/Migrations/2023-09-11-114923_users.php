<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TestMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }
    public function down()
    {
        // Define how to reverse the changes made in the up() method.
        // In this example, we'll drop the 'users' table.
        $this->forge->dropTable('users');
    }
}
