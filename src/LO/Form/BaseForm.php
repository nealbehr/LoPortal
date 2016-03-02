<?php
/**
 * User: Eugene Lysenko
 * Date: 2/11/16
 * Time: 14:54
 */

namespace LO\Form;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\Exception\InvalidConfigurationException;

class BaseForm extends AbstractType
{
    const PATTERN_EMAIL = '/^[_a-z0-9]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';

    public function getName()
    {
        throw new InvalidConfigurationException('Not implemented method getName().');
    }
}
