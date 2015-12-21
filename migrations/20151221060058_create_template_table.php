<?php

use Phinx\Migration\AbstractMigration;

class CreateTemplateTable extends AbstractMigration
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
        $this->execute(<<<EOL
CREATE TABLE `template` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`deleted` enum('0','1') NOT NULL DEFAULT '0',
`name` varchar(50) DEFAULT NULL,
`description` text DEFAULT NULL,
`picture` varchar(255) default null,
`created_at` datetime DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE `template`');
    }
}
