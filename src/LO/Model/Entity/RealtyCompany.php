<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/22/15
 * Time: 12:03
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(name="realty_company")
 */
class RealtyCompany extends Base {

    const NO_LOGO_PICTURE = 'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/logo-stub.png';

    /**
     * @Column(type="string", length=50)
     * @Assert\NotBlank(message="Name should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 50,
     *              maxMessage = "Name cannot be longer than {{ limit }} characters" )
     *
     */
    protected $name;

    /**
     * @Column(type="string", length=255)
     */
    protected $logo;

    /**
     * @Column(type="boolean")
     */
    protected $deleted;

    function __construct()
    {
        parent::__construct();
        $this->deleted = false;
        $this->logo = self::NO_LOGO_PICTURE;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param mixed $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function toArray() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
        );
    }
} 