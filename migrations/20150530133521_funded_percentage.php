<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class FundedPercentage extends AbstractMigration
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
        $request = $this->table('request_flyer');
        $request->changeColumn('listing_price', 'decimal', array('precision' => 15, 'scale' => 0, 'default' => 0 ));
        $request->addColumn('funded_percentage', 'decimal', array('precision' => 4, 'scale' => 2, 'after' => 'listing_price', 'default' => 10.00));
        $request->addColumn('maximum_loan', 'decimal', array('precision' => 4, 'scale' => 2, 'after' => 'funded_percentage', 'default' => 80.00));
//        $request->removeColumn('pdf_link');
        $request->update();
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