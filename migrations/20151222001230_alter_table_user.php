<?php

use Phinx\Migration\AbstractMigration;

class AlterTableUser extends AbstractMigration
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
        $this->execute('ALTER TABLE users MODIFY updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP;');

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}