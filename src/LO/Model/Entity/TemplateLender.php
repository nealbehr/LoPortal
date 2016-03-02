<?php
/**
 * User: Eugene Lysenko
 * Date: 12/22/15
 * Time: 15:45
 */
namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="template_lender")
 */
class TemplateLender
{
    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Template id should not be blank.", groups = {"main"})
     */
    private $template_id;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Lender id should not be blank.", groups = {"main"})
     */
    private $lender_id;
}
