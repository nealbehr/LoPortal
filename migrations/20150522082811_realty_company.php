<?php

use Phinx\Migration\AbstractMigration;

class RealtyCompany extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     */
    public function change()
    {
        // create the table
        $table = $this->table('realty_company');
        $table->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('deleted', 'boolean', ['limit' => 255, 'default' => false])
            ->addColumn('logo', 'string', ['limit' => 255])
            ->addTimestamps()
            ->addIndex(array('deleted'))
            ->create();

        $this->execute('insert into realty_company(id, name, logo) values (1, "Alain Pinel", "https://s3.amazonaws.com/1rex.realty.logo/14323945558968.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (2, "Insignia Mortgage", "https://s3.amazonaws.com/1rex.realty.logo/143230346365938.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (3, "J. Rockcliff Mortgage", "https://s3.amazonaws.com/1rex.realty.logo/143239038930129.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (4, "John L Scott Mortgage", "https://s3.amazonaws.com/1rex.realty.logo/143239065380785.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (5, "Pacific Union", "https://s3.amazonaws.com/1rex.realty.logo/143239407478962.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (6, "Realogics Sotheby", "https://s3.amazonaws.com/1rex.realty.logo/143239363147637.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (7, "RealtyOne", "https://s3.amazonaws.com/1rex.realty.logo/143239424956694.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (8, "Redfin", "https://s3.amazonaws.com/1rex.realty.logo/143239441389146.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (9, "REMAX", "https://s3.amazonaws.com/1rex.realty.logo/143239081382109.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (10, "REMAX Estate", "https://s3.amazonaws.com/1rex.realty.logo/143239081382109.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (11, "Rockwell Realty", "https://s3.amazonaws.com/1rex.realty.logo/143239098536592.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (12, "RSVP Real Estate", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (13, "Skyline Properties", "https://s3.amazonaws.com/1rex.realty.logo/143239004842814.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (14, "Windermere Realty", "https://s3.amazonaws.com/1rex.realty.logo/14323901774058.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (15, "Windermere Realty Maple Valley", "https://s3.amazonaws.com/1rex.realty.logo/14323901774058.JPEG")');
        $this->execute('insert into realty_company(id, name, logo) values (16, "Winkelmann", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (17, "Better Properties Seattle King", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (18, "Better Properties Solutions", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (19, "First Team", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (20, "Grubb Realty", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (21, "Hallmark", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (22, "Heritage Sotheby\'s", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (23, "Indian Valley", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (24, "McGuire Real Estate", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (25, "NW Choice Realty", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (26, "Pillar NorthWest Real Estate", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (27, "Podley Properties", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (28, "REMAX Integrity", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (29, "Sotheby\'s Realty", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (30, "Windermere Realty East", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (31, "Keller Williams (white background)", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');
        $this->execute('insert into realty_company(id, name, logo) values (32, "Keller Williams (no background)", "https://s3.amazonaws.com/1rex.realty.logo/no-logo.png")');

    }
    
    /**
     * Migrate Up.
     */
    public function up()
    {
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}