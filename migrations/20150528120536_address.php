<?php

use Phinx\Migration\AbstractMigration;

class Address extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     */
    public function change()
    {
        // create the table
        $table = $this->table('address');
        $table->addColumn('formatted_address', 'string', array('limit' => 255, 'null'=> true));
        $table->addColumn('street_number', 'string', array('limit' => 64, 'null'=> true));
        $table->addColumn('route', 'string', array('limit' => 64, 'null'=> true));
        $table->addColumn('locality', 'string', array('limit' => 30, 'null'=> true));
        $table->addColumn('administrative_area_level_1', 'string', array('limit' => 2, 'null'=> true));
        $table->addColumn('postal_code', 'string', array('limit' => 10, 'null'=> true));
        $table->addTimestamps();
        $table->create();
    }
    
    /**
     * Migrate Up.
     */
    public function up()
    {
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}