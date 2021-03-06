<?php

use Phinx\Migration\AbstractMigration;

class CreateRequestApprovalTable extends AbstractMigration
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
CREATE TABLE `request_approval` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  queue_id int(11) unsigned,
  `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`id`),
  key fk_queue_id(queue_id),
  CONSTRAINT `fk_request_approval_queue_id` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('request_approval');
    }
}