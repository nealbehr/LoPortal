<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 7:24 PM
 */

namespace LO\Bridge\Doctrine\Hydrator;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

class Duplicates extends AbstractHydrator{
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

        $id = $row[0];

        if(!isset($result[$id])){

            $result[$id] = [];
        }

        array_shift($row);
        $item = array_combine(['id', 'created_at'], $row);
        $item['created_at'] = \DateTime::createFromFormat('Y-m-d H:i:s', $item['created_at']);
        $result[$id][] = $item;
    }
}