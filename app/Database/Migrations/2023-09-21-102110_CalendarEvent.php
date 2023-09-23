<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CalendarEvent extends Migration
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
                'calendar_id' => [
                    'type' => 'INT',
                    'constraint' => 5,
                    'unsigned' => true,
                ],
                'DTSTART' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                ],
                'DTEND' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                ],
                'SUMMARY' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                ],
                'DESCRIPTION' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'LOCATION' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                ],
                'createdAt' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updatedAt' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('calendar_id', 'calendars', 'id');
            $this->forge->createTable('events'); // Create the 'events' table
        
            // No need to create the 'calendars' table here
        }
        

    public function down()
    {
        //
    }
}
