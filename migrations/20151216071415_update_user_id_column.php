<?php

use Phinx\Migration\AbstractMigration;

class UpdateUserIdColumn extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('UPDATE queue_realtor a JOIN queue b ON a.id = b.realtor_id SET a.user_id = b.user_id;');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}