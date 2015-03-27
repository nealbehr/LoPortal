<?php

use Phinx\Migration\AbstractMigration;

class CreateQueueTable extends AbstractMigration
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
CREATE TABLE `queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  firstrex_id INT(11),
  request_type TINYINT(4),
  realtor_id int(11) unsigned not null,
  mls_number varchar(50),
  pdf_link text,
  state tinyint(4),
  address varchar(255),
  photo text,
  `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`id`),
  key fk_realtor_id(realtor_id),
  CONSTRAINT `fk_queue_realtor_id` FOREIGN KEY (`realtor_id`) REFERENCES `realtor` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('queue');
    }
}