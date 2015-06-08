<?php

use Phinx\Migration\AbstractMigration;

class UserSalesDirectorPhone extends AbstractMigration
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
        $users->addColumn('sales_director_phone', 'string', array('null'=> true, 'after' => 'sales_director_email', 'limit' => 100));
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