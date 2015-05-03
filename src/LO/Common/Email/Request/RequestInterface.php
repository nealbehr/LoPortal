<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 2:07 PM
 */

namespace LO\Common\Email\Request;


interface RequestInterface {
    /**
     * @return string
     */
    public function getSubject();

    /**
     * @return string
     */
    public function getTemplateName();

    /**
     * @return array
     */
    public function getTemplateVars();
} 