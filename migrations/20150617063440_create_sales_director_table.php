<?php

use Phinx\Migration\AbstractMigration;

class CreateSalesDirectorTable extends AbstractMigration
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
        $this->execute(
            "CREATE TABLE `sales_director` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `deleted` enum('0','1') NOT "
            ."NULL DEFAULT '0', `name` varchar(255) DEFAULT NULL, `email` varchar(50) NOT NULL, `phone` varchar(100) "
            ."DEFAULT NULL, `created_at` datetime DEFAULT NULL, `updated_at` datetime DEFAULT NULL, PRIMARY KEY (`id`),"
            ." UNIQUE KEY `email_unique` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('sales_director');
    }
}
