<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 3:30 PM
 */

namespace LO\Controller\Admin;


class Base
{
    const LIMIT         = 20;
    const KEY_SEARCH_BY = 'searchBy';
    const KEY_SEARCH    = 'filterValue';
    const KEY_PAGE      = 'page';
    const KEY_SORT      = 'sort';
    const KEY_DIRECTION = 'direction';

    protected function getOrderDirection($direction, $defaultDirection){
        return in_array(strtolower($direction), ['asc', 'desc'])? $direction: $defaultDirection;
    }

} 