<?php

use Phinx\Migration\AbstractMigration;

class CreateUserTable extends AbstractMigration
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
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50),
  `last_name` varchar(50),
  `email` varchar(50) NOT NULL,
  `gender` enum('male', 'female') NULL,
  `password` varchar(255) NOT NULL,
  picture varchar(255) default null,
  state tinyINt(4) default null,
  `roles` varchar(255) NOT NULL,
   `created_at` datetime default null,
  `updated_at` datetime default null,
  PRIMARY KEY (`id`),
  CONSTRAINT email_unique UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOL
);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('users');
    }
}