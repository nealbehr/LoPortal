<?php

use Phinx\Migration\AbstractMigration;

class CreateTemplateAddressTable extends AbstractMigration
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
CREATE TABLE template_address (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    template_id int(11) unsigned NOT NULL,
    state char(2) NOT NULL,
    PRIMARY KEY(id),
    UNIQUE KEY `template_id` (`template_id`,`state`),
    CONSTRAINT `ltemplate_address_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE template_state ADD FOREIGN KEY (template_id) REFERENCES template(id);
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}