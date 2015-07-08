<?php

use Phinx\Migration\AbstractMigration;

class AddStatuses extends AbstractMigration
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
        $table = 'statys';
        $data  = [[
            'type' => 'approve',
            'name' => 'Approved',
            'text' => 'Your property has been approved for REX HomeBuyer.'
        ], [
            'type' => 'approve',
            'name' => 'Approved with Conditions',
            'text' => 'Your property is approved with additional conditions.  Please call your Sales Director for details.'
        ], [
            'type' => 'approve',
            'name' => 'Approved with 25% down',
            'text' => 'Your Property Approval request has been approved for REX HomeBuyer. Purchase requires a combined'
                .' 25% down payment.'
        ], [
            'type' => 'approve',
            'name' => 'Approved with 25% down with conditions',
            'text' => 'Your Property Approval request has been approved for REX HomeBuyer. Purchase requires a combined'
                .' 25% down payment. There are additional conditions.  Please call your Sales Director for details.'
        ], [
            'type' => 'decline',
            'name' => 'Typicality',
            'text' => 'Property is not typical for the area.  Attributes considered include but aren’t limited to home '
                .'type, listing price, lot size, square footage etc.'
        ], [
            'type' => 'decline',
            'name' => 'Supply Abundance',
            'text' => 'Property is located in an area with a surplus of developable land.'
        ], [
            'type' => 'decline',
            'name' => 'Investment Guidelines',
            'text' => 'Property does not fall within our Investment Guidelines, which include but aren’t limited to: '
                .'property type or condition, listing price or location.'
        ], [
            'type' => 'decline',
            'name' => 'New Construction in a 25% zone',
            'text' => 'Down payment funding is unavailable for newly constructed homes that fall within areas requiring'
                .' a 25% down payment.'
        ], [
            'type' => 'decline',
            'name' => 'Rural',
            'text' => 'Property is in an area with a population density that falls below our guidelines.'
        ]];

        foreach ($data as $array) {
            $this->execute(
                "insert into $table(".implode(", ", array_keys($array)).") values ('".implode("', '", $array)."')"
            );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
