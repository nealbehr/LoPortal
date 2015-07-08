<?php
use Phinx\Migration\AbstractMigration;

class AddForeignKeyInQueueTable extends AbstractMigration
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
        $this->execute("ALTER TABLE `queue` ADD `status_id` int(11) unsigned DEFAULT NULL AFTER `user_id`;");
        $this->execute("ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status`"
            ." (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("ALTER TABLE `queue` ADD `status_id` int(11) unsigned DEFAULT NULL AFTER `user_id`;");
        $this->execute("ALTER TABLE `queue` ADD CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status`"
            ." (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;");
    }
}
