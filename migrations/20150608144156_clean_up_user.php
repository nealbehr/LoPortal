<?php

use Phinx\Migration\AbstractMigration;

class CleanUpUser extends AbstractMigration
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
        $queue = $this->table('users');
        $queue->removeColumn('street');
        $queue->removeColumn('city');
        $queue->removeColumn('province');
        $queue->removeColumn('zip_code');
        $queue->removeColumn('account_name');
        $queue->removeColumn('territory');
        $queue->removeColumn('pmp');
        $queue->update();
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