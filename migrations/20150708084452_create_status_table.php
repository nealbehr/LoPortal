<?php

use Phinx\Migration\AbstractMigration;

class CreateStatusTable extends AbstractMigration
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
        $this->execute("CREATE TABLE `status` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `type` enum('approve',"
            ."'decline') DEFAULT NULL, `name` varchar(50) DEFAULT NULL, `text` text, PRIMARY KEY (`id`)) ENGINE=InnoDB "
            ."DEFAULT CHARSET=utf8;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('status');
    }
}
