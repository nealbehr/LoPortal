<?php

use Phinx\Migration\AbstractMigration;

class TemplateAddColumnCoBranded extends AbstractMigration
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
ALTER TABLE template ADD co_branded enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '0' AFTER format_id;
UPDATE template SET co_branded = '1' WHERE lenders_all = '0' OR states_all = '0';
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