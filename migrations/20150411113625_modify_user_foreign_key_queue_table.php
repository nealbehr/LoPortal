<?php

use Phinx\Migration\AbstractMigration;

class ModifyUserForeignKeyQueueTable extends AbstractMigration
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
        $this->execute("
ALTER TABLE queue DROP FOREIGN KEY queue_ibfk_1;

ALTER TABLE `queue`
ADD CONSTRAINT `queue_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
ALTER TABLE queue DROP FOREIGN KEY queue_ibfk_1;

ALTER TABLE `queue`
ADD CONSTRAINT `queue_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
        ");
    }
}