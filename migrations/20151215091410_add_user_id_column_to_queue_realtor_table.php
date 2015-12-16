<?php

use Phinx\Migration\AbstractMigration;

class AddUserIdColumnToQueueRealtorTable extends AbstractMigration
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
        $this->execute('ALTER TABLE `queue_realtor` ADD `user_id` int(11) DEFAULT NULL AFTER `id`;');
        $this->execute('ALTER TABLE `queue_realtor` ADD INDEX `qr_user_id` (`user_id`);');
        $this->execute('ALTER TABLE `queue_realtor` ADD INDEX `qr_id_user_id` (`id`, `user_id`);');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
