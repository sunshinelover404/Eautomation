<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Calendar extends Migration
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
            'calname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'caldescription' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'caldavlink' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'owner' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('calendars');
    }

    public function down()
    {
        //
    }
}
