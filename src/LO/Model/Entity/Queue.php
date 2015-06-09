<?php
namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Entity
 * @Table(name="queue")
 */
class Queue extends Base {

    const DEFAULT_FUNDED_PERCENTAGE = 10;
    const DEFAULT_MAXIMUM_LOAN = 80;

    const STATE_LISTING_FLYER_PENDING = 1;
    const STATE_REQUESTED   = 2;
    const STATE_APPROVED    = 3;
    const STATE_DECLINED    = 4;
    const STATE_DRAFT       = 5;

    const TYPE_FLYER             = 1;
    const TYPE_PROPERTY_APPROVAL = 2;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Firstrex id should not be blank.", groups = {"main"})
     * @Assert\Type(type="numeric")
     */
    protected $firstrex_id;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank(message="Request type should not be blank.")
     */
    protected $request_type;

    /**
     * @Column(type="string", length=255)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $mls_number;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank(message="State should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $state;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message="Address should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $address;

    /**
     * @Column(type="integer")
     */
    protected $user_id;

    /**
     * @Column(type="string")
     */
    protected $reason;

    /**
     * @Column(type="array")
     */
    protected $additional_info;

    /**
     * @OneToOne(targetEntity="User", fetch="LAZY")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @Column(type="float")
     * @Assert\GreaterThanOrEqual(value = 0, message = "Listing price should not be blank.", groups = {"main"})
     */
    protected $listing_price;

    /**
     * @Column(type="float")
     * @Assert\NotBlank(message = "FirstREX Funded Percentage should not be blank.", groups = {"main"})
     */
    protected $funded_percentage;

    /**
     * @Column(type="float")
     * @Assert\NotBlank(message = "Maximum Loan Amount should not be blank.", groups = {"main"})
     */
    protected $maximum_loan;

    /**
     * @Column(type="string")
     * @Assert\NotBlank(message = "Photo id should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @OneToOne(targetEntity="Realtor", fetch="LAZY")
     * @JoinColumn(name="realtor_id", referencedColumnName="id")
     **/
    private $realtor;

    public function __construct(){
        parent::__construct();

        $this->funded_percentage = self::DEFAULT_FUNDED_PERCENTAGE;
        $this->maximum_loan = self::DEFAULT_MAXIMUM_LOAN;
        $this->photo = '';
        $this->state = self::STATE_REQUESTED;
    }

    public function getAddress(){
        return $this->address;
    }

    public function getMlsNumber(){
        return $this->mls_number;
    }

    public function setAdditionalInfo($param){
        $this->additional_info = $param;

        return $this;
    }

    public function getAdditionalInfo(){
        return $this->additional_info;
    }

    public function set1RexId($param){
        $this->firstrex_id = $param;

        return $this;
    }

    public function setType($param){
        $this->request_type = $param;

        return $this;
    }

    public function getType(){
        return $this->request_type;
    }

    public function setUserId($param){
        $this->user_id = $param;

        return $this;
    }

    public function setMlsNumber($param){
        $this->mls_number = $param;

        return $this;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($param){
        $this->state = $param;

        return $this;
    }

    public function setAddress($param){
        $this->address = $param;

        return $this;
    }

    public function setReason($param){
        $this->reason = $param;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user){
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFundedPercentage()
    {
        return $this->funded_percentage;
    }

    /**
     * @param mixed $funded_percentage
     */
    public function setFundedPercentage($funded_percentage)
    {
        $this->funded_percentage = $funded_percentage;
    }

    /**
     * @return mixed
     */
    public function getListingPrice()
    {
        return $this->listing_price;
    }

    /**
     * @param mixed $listing_price
     */
    public function setListingPrice($listing_price)
    {
        $this->listing_price = $listing_price;
    }

    /**
     * @return mixed
     */
    public function getMaximumLoan()
    {
        return $this->maximum_loan;
    }

    /**
     * @param mixed $maximum_loan
     */
    public function setMaximumLoan($maximum_loan)
    {
        $this->maximum_loan = $maximum_loan;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return Realtor
     */
    public function getRealtor()
    {
        return $this->realtor;
    }

    /**
     * @param Realtor $realtor
     */
    public function setRealtor($realtor)
    {
        $this->realtor = $realtor;
    }

    /**
     * @Assert\Callback(groups = {"main"})
     */
    public function isStateValid(ExecutionContextInterface $context){
        $allowedStates = static::TYPE_FLYER == $this->request_type
            ? Queue::getAllowedStates()
            : RequestApproval::getAllowedStates();
/** @todo поиграться повесь колбек на само поле state */
        if (!in_array($this->getState(), $allowedStates)) {
            $context->addViolationAt(
                'state',
                sprintf('Field "State" have not contained allowed states. Allowed states for type \'%d\' are [%s]', $this->request_type, implode(', ', $allowedStates)),
                array(),
                null
            );
        }

        if(static::TYPE_FLYER !== $this->request_type && $this->state === static::STATE_LISTING_FLYER_PENDING){
            $context->addViolationAt(
                'state',
                '"APPROVED" state can only be a request flyer with the downloaded pdf file.',
                array(),
                null
            );
        }
    }

    /**
     * @Assert\Callback(groups = {"draft"})
     */
    public function isStateValidDraft(ExecutionContextInterface $context){
        if ($this->getState() != static::STATE_DRAFT) {
            $context->addViolationAt(
                'state',
                sprintf('Field "State" have not contained allowed state. Allowed state for draft is "%s"', static::STATE_DRAFT),
                array(),
                null
            );
        }
    }

    /**
     * @Assert\Callback(groups = {"approved"})
     */
    public function isStateValidApproved(ExecutionContextInterface $context){
        if ($this->getState() != static::STATE_APPROVED) {
            $context->addViolationAt(
                'state',
                sprintf('Field "State" have not contained allowed state. Allowed state for draft is "%s"', static::STATE_APPROVED),
                array(),
                null
            );
        }
    }

    /**
     * @Assert\Callback(groups = {"fromPropertyApproval"})
     */
    public function isStateValidFromPropertyApproval(ExecutionContextInterface $context){
        if ($this->getState() != static::STATE_LISTING_FLYER_PENDING) {
            $context->addViolationAt(
                'state',
                sprintf('Field "State" have not contained allowed state. Allowed state for draft is "%s"', static::STATE_LISTING_FLYER_PENDING),
                array(),
                null
            );
        }
    }

    static public function getTypes(){
        return [
            static::TYPE_PROPERTY_APPROVAL,
            static::TYPE_FLYER,
        ];
    }

    static public function getStates(){
        static $states = [];

        if(!count($states)){
            $oClass = new \ReflectionClass(__CLASS__);
            foreach($oClass->getConstants() as $k => $v){
                if(strpos($k, "STATE_") !== 0){
                    continue;
                }

                $states[] = $v;
            }
        }

        return $states;
    }

    public static function getAllowedStates(){
        return [
            Queue::STATE_REQUESTED,
            Queue::STATE_APPROVED,
            Queue::STATE_DECLINED,
            Queue::STATE_LISTING_FLYER_PENDING,
        ];
    }
} 