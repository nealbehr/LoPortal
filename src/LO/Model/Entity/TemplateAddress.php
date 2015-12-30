<?php
/**
 * User: Eugene Lysenko
 * Date: 12/22/15
 * Time: 15:45
 */
namespace LO\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity
 * @Table(name="template_address")
 */
class TemplateAddress
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
     * @ManyToOne(targetEntity="Template", inversedBy="addresses")
     * @JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @Column(type="string")
     */
    private $state;

    public function getId()
    {
        return $this->id;
    }

    public function setId($params)
    {
        $this->id = $params;
        return $this;
    }


    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate(Template $param)
    {
        $this->template = $param;
        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($param)
    {
        $this->state = $param;
        return $this;
    }
}
