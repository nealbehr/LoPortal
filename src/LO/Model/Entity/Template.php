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
    private $deleted    = '0';

    /**
     * @Column(type="string")
     */
    protected $archive = '0';

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
    protected $co_branded  = '0';

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
     *              maxMessage = "Preview picture url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $preview_picture;

    /**
     * @Column(type="string", length=65536)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Type cannot be longer than {{ limit }} characters"
     * )
     */
    protected $file_format;

    /**
     * @Column(type="string", length=65536)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "File url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $file;

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
        parent::__construct();
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

    public function getArchive()
    {
        return $this->archive;
    }

    public function setArchive($param)
    {
        $this->archive = $param;
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

    public function getCoBranded()
    {
        return $this->co_branded;
    }

    public function setCoBranded($param)
    {
        $this->co_branded = $param;
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

    public function getPreviewPicture()
    {
        return $this->preview_picture;
    }

    public function setPreviewPicture($param)
    {
        $this->preview_picture = $param;
        return $this;
    }

    public function getFileFormat()
    {
        return $this->file_format;
    }

    public function setFileFormat($param)
    {
        $this->file_format = $param;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($param)
    {
        $this->file = $param;
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

    /**
     * @return bool
     */
    public function isCoBranded()
    {
        return (bool)$this->co_branded;
    }

    /**
     * @return bool
     */
    public function forAllLenders()
    {
        return (bool)$this->lenders_all;
    }

    /**
     * @return bool
     */
    public function forAllStates()
    {
        return (bool)$this->states_all;
    }

    public function toFullArray()
    {
        $states  = array_map(function($object) { return $object->getState(); }, $this->getAddresses()->getValues());
        $lenders = array_map(function($object) { return $object->getId(); }, $this->getLenders()->getValues());

        return array(
            'id'              => $this->id,
            'archive'         => $this->archive,
            'category_id'     => $this->category_id,
            'format_id'       => $this->format_id,
            'co_branded'      => $this->co_branded,
            'lenders_all'     => $this->lenders_all,
            'states_all'      => $this->states_all,
            'name'            => $this->name,
            'description'     => $this->description,
            'preview_picture' => $this->preview_picture,
            'file_format'     => $this->file_format,
            'file'            => $this->file,
            'states'          => $states,
            'lenders'         => $lenders
        );
    }
}
