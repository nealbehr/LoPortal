<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/14/15
 * Time: 5:23 PM
 */

namespace LO\Bridge\Doctrine\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

class ListItems extends AbstractHydrator{
    protected function hydrateAllData(){
        $result = [];
        foreach($this->_stmt->fetchAll(\PDO::FETCH_NUM) as $row) {
            $this->hydrateRowData($row, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $row,  array &$result){
        if(count($row) == 0) {
            return false;
        }

        $result[] = $row[0];
    }
}