<?php
/**
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 15:15
 */
namespace LO\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;
use LO\Validator\FullName;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity
 * @Table(name="template")
 */
class Template extends Base
{
    /**
     * @Column(type="string")
     */
    private $deleted = '0';

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Category id should not be blank.", groups = {"main"})
     */
    protected $category_id;

    /**
     * @OneToOne(targetEntity="TemplateCategory", fetch="LAZY")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Format id should not be blank.", groups = {"main"})
     */
    protected $format_id;

    /**
     * @OneToOne(targetEntity="TemplateFormat", fetch="LAZY")
     * @JoinColumn(name="format_id", referencedColumnName="id")
     */
    protected $format;

    /**
     * @Column(type="string")
     */
    protected $lenders_all = '1';

    /**
     * @Column(type="string")
     */
    protected $states_all = '1';

    /**
     * @Column(type="string", length=50)
     * @Assert\NotBlank(message="Name should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 50,
     *              maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $name;

    /**
     * @Column(type="string")
     */
    protected $description;

    /**
     * @Column(type="string", length=65536)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Picture url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $picture;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="Lender", inversedBy="template", cascade={"remove", "persist"})
     * @JoinTable(name="template_lender")
     */
    private $lenders;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="TemplateAddress", mappedBy="template", cascade={"remove", "persist"})
     */
    private $addresses;

    public function __construct()
    {
        $this->lenders   = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($param)
    {
        $this->deleted = $param;
        return $this;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function setCategoryId($param)
    {
        $this->category_id = $param;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(TemplateCategory $param)
    {
        $this->category = $param;
        return $this;
    }

    public function getFormatId()
    {
        return $this->format_id;
    }

    public function setFormatId($param)
    {
        $this->format_id = $param;
        return $this;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat(TemplateFormat $param)
    {
        $this->format = $param;
        return $this;
    }

    public function getLendersAll()
    {
        return $this->lenders_all;
    }

    public function setLendersAll($param)
    {
        $this->lenders_all = $param;
        return $this;
    }

    public function getStatesAll()
    {
        return $this->states_all;
    }

    public function setStatesAll($param)
    {
        $this->states_all = $param;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($param)
    {
        $this->name = $param;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($param)
    {
        $this->description = $param;
        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($param)
    {
        $this->picture = $param;
        return $this;
    }

    public function getLenders()
    {
        return $this->lenders;
    }

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function toFullArray()
    {
        $states = [];
        if (($objects = $this->getAddresses()) !== null) {
            foreach($objects as $object) {
                $states[] = $object->getState();
            }
        }

        $lenders = [];
        if (($objects = $this->getLenders()) !== null) {
            foreach($objects as $object) {
                $lenders[] = $object->getId();
            }
        }

        return array(
            'id'          => $this->id,
            'category_id' => $this->category_id,
            'format_id'   => $this->format_id,
            'lenders_all' => $this->lenders_all,
            'states_all'  => $this->states_all,
            'name'        => $this->name,
            'description' => $this->description,
            'picture'     => $this->picture,
            'states'      => $states,
            'lenders'     => $lenders
        );

    }
}
