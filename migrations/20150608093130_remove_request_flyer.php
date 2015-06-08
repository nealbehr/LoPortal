<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class RemoveRequestFlyer extends AbstractMigration
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
        $table = $this->table('queue');
        $table->addColumn('realtor_id', 'integer', array('limit' => MysqlAdapter::INT_REGULAR, 'signed' => false, 'after' => 'state', 'null'=> true));
        $table->addColumn('listing_price', 'decimal', array('precision' => 15, 'scale' => 0, 'default' => 0, 'after' => 'realtor_id' ));
        $table->addColumn('funded_percentage', 'decimal', array('precision' => 4, 'scale' => 2, 'after' => 'listing_price', 'default' => 10.00));
        $table->addColumn('maximum_loan', 'decimal', array('precision' => 4, 'scale' => 2, 'after' => 'funded_percentage', 'default' => 80.00));
        $table->addColumn('photo', 'text', array('after' => 'maximum_loan', 'null'=> true));
        $table->update();

        $rows = $this->fetchAll('SELECT * FROM request_flyer');
        foreach($rows as $row) {
            $queue_id = $row['queue_id'];
            $realtor_id = $row['realtor_id'];
            $listing_price = $row['listing_price'];
            $funded_percentage = $row['funded_percentage'];
            $maximum_loan = $row['maximum_loan'];
            $photo = $row['photo'];

            $query = "update queue set realtor_id='" . $realtor_id ."', listing_price='". $listing_price
                . "', funded_percentage='" . $funded_percentage. "', maximum_loan='" . $maximum_loan . "', photo='"
                . $photo . "' where id=" . $queue_id;

            $this->query($query);
        }

        $queue = $this->table('queue');
        $queue->addForeignKey('realtor_id', 'realtor', 'id', array('delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'));
        $queue->update();

        $this->query('drop table request_flyer');
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