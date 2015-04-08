<?php

use Phinx\Migration\AbstractMigration;

class CreateRequestFlyerTable extends AbstractMigration
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
CREATE TABLE `request_flyer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  queue_id int(11) unsigned,
  realtor_id int(11) unsigned not null,
  pdf_link text,
  photo text,
  `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`id`),
  key fk_realtor_id(realtor_id),
  key fk_queue_id(queue_id),
  CONSTRAINT `fk_request_flyer_realtor_id` FOREIGN KEY (`realtor_id`) REFERENCES `realtor` (`id`),
  CONSTRAINT `fk_request_flyer_queue_id` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('request_flyer');
    }
}