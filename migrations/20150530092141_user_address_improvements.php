<?php

use Phinx\Migration\AbstractMigration;

class UserAddressImprovements extends AbstractMigration
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
        $address = $this->table('address');
        $address->addColumn('place_id', 'string', array('null'=> true, 'after' => 'id', 'limit' => 100));
        $address->renameColumn('route', 'street');
        $address->renameColumn('locality', 'city');
        $address->renameColumn('administrative_area_level_1', 'state');
        $address->update();
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