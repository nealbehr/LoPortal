<?php

use Phinx\Migration\AbstractMigration;

class CreateNewRealtorTable extends AbstractMigration
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
        $this->execute("CREATE TABLE `realtor` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `deleted` enum('0','1') "
            ."NOT NULL DEFAULT '0', `realty_company_id` int(11) DEFAULT NULL, `first_name` varchar(50) DEFAULT NULL, "
            ."`last_name` varchar(50) DEFAULT NULL, `bre_number` varchar(255) DEFAULT NULL, `phone` varchar(100) "
            ."DEFAULT NULL, `email` varchar(50) DEFAULT NULL, `photo` text, `created_at` datetime DEFAULT NULL, "
            ."`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `email_unique` "
            ."(`email`), UNIQUE KEY `first_last_name_unique` (`first_name`,`last_name`)) ENGINE=InnoDB DEFAULT "
            ."CHARSET=utf8;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('realtor');
    }
}
