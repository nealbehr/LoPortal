<?php

use Phinx\Migration\AbstractMigration;

class CreateRealtorTable extends AbstractMigration
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
CREATE TABLE `realtor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bre_number` varchar(255) DEFAULT NULL,
  `estate_agency` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `photo` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('realtor');
    }
}