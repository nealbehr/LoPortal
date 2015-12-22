<?php

use Phinx\Migration\AbstractMigration;

class CreateTemplateCategoryTable extends AbstractMigration
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
CREATE TABLE `template_category` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(50) DEFAULT NULL,
PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `template_category` (`name`) VALUES ('Consumer'), ('Real Estate'), ('Affinity'), ('Program Reference');
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE `template_category`');
    }
}