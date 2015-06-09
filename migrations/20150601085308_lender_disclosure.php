<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class LenderDisclosure extends AbstractMigration
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
        $table = $this->table('lender_disclosure');
        $table->addColumn('lender_id', 'integer', array('signed' => false));
        $table->addColumn('state', 'string', array('limit' => 2, 'null'=> true));
        $table->addColumn('disclosure', 'text');
        $table->addTimestamps();
        $table->addIndex(array('lender_id', 'state'), array('unique' => true));
        $table->create();

        $this->execute("alter table `lender_disclosure` add foreign key (`lender_id` ) references `lender` (`id` )");

        $lenders = $this->fetchAll('SELECT * FROM lender');
        foreach($lenders as $lender) {
            $this->execute("insert into `lender_disclosure` (`lender_id`, `state`, `disclosure`)
             values ('$lender[id]', 'US', '$lender[disclosure]');");
        }

        $request = $this->table('lender');
        $request->removeColumn('address');
        $request->removeColumn('disclosure');
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