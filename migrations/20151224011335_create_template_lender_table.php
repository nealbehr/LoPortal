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
    template_id INT NOT NULL,
    lender_id INT NOT NULL,
    PRIMARY KEY(template_id, lender_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE users_groups ADD FOREIGN KEY (template_id) REFERENCES template(id);
ALTER TABLE users_groups ADD FOREIGN KEY (lender_id) REFERENCES lender(id);
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