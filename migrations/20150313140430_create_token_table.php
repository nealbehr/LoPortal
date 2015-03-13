<?php

use Phinx\Migration\AbstractMigration;

class CreateTokenTable extends AbstractMigration
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
CREATE TABLE `tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expiration_time` datetime NOT NULL,
  `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`id`),
  INDEX tokens_hash(hash),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('tokens');
    }
}