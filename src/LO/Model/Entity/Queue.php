<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 3:17 PM
 */

namespace LO\Model\Entity;

class Queue extends Base{
    const STATE_IN_PROGRESS = 1;
    const STATE_REQUESTED   = 2;
    const STATE_APPROVED    = 3;

    /**
     * @Column(type="integer")
     */
    protected $firstrex_id;

    /**
     * @Column(type="smallint")
     */
    protected $request_type;

    /**
     * @Column(type="integer")
     */
    protected $realtor_id;

    /**
     * @Column(type="string", length=255)
     */
    protected $mls_number;

    /**
     * @Column(type="string")
     */
    protected $pdf_link;

    /**
     * @Column(type="smallint")
     */
    protected $state;

    /**
     * @Column(type="string", length=255)
     */
    protected $address;

    /**
     * @Column(type="string")
     */
    protected $photo;

    public function __construct(){
        parent::__construct();

        $this->state = self::STATE_REQUESTED;
    }

    public function set1RexId($param){
        $this->firstrex_id = $param;

        return $this;
    }
} 