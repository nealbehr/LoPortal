<?php

use Phinx\Migration\AbstractMigration;

class AlterTemplateTableAddCols extends AbstractMigration
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
            ALTER TABLE template ADD file_format char(3) COLLATE 'utf8_unicode_ci' NULL AFTER archive;
            ALTER TABLE template ADD file varchar(255) DEFAULT NULL AFTER picture;
            ALTER TABLE template CHANGE `picture` `preview_picture` varchar(255) COLLATE 'utf8_general_ci' NULL;
            UPDATE template SET file_format = 'jpg', file = preview_picture WHERE file IS NULL;
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
