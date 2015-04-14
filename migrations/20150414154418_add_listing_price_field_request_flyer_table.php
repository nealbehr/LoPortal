<?php

use Phinx\Migration\AbstractMigration;

class AddListingPriceFieldRequestFlyerTable extends AbstractMigration
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
        $this->execute("ALTER TABLE request_flyer ADD COLUMN listing_price DOUBLE NOT NULL;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("ALTER TABLE request_flyer DROP COLUMN listing_price;");
    }
}