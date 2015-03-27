<?php

use Phinx\Migration\AbstractMigration;

class AddOwnerFieldQueueTable extends AbstractMigration
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
        $this->execute('
ALTER TABLE queue ADD COLUMN user_id INT(11) unsigned NOT NULL;
update queue set user_id = 1;
ALTER TABLE queue ADD FOREIGN KEY (user_id) REFERENCES users(id);
        ');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('
ALTER TABLE queue DROP COLUMN user_id;
        ');
    }
}