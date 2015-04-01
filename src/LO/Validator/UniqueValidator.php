<?php
/**
 * Apps-O-Rama (Face It)
 * Project: Broadway
 * User: Denys Pishchenko
 * Date: 05.01.15
 * Time: 13:10
 */

namespace LO\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

/**
 * @Annotation
 */
class UniqueValidator extends ConstraintValidator {

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em){
        $this->setEm($em);
    }

    public function validate($value, Constraint $constraint)
    {
        $exists = $this->em
            ->getRepository($constraint->entity)
            ->findOneBy(array($constraint->field => $value));

        if ($exists) {
            $this->context->addViolation($constraint->notUniqueMessage, array('%string%' => $value));

            return false;
        }

        return true;
    }

    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }
}