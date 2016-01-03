<?php

use Phinx\Migration\AbstractMigration;

class CreateTemplateLenderTable extends AbstractMigration
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
CREATE TABLE template_lender (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    template_id INT NOT NULL,
    lender_id INT NOT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `template_lender` ADD INDEX `template_id` (`template_id`), ADD INDEX `lender_id` (`lender_id`);
ALTER TABLE template_lender ADD FOREIGN KEY (template_id) REFERENCES template(id);
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