<?php

use Phinx\Migration\AbstractMigration;

class UserAddress extends AbstractMigration
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
        $users = $this->table('users');
        $users->addColumn('address_id', 'integer', array('null'=> true, 'after' => 'id'));
        $users->addForeignKey('address_id', 'address', 'id', array('delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'));
        $users->update();
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