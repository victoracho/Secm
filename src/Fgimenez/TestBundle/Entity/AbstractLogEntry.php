<?php

namespace Fgimenez\TestBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fgimenez\TestBundle\Entity
 * @ORM\MappedSuperclass
 */
abstract class AbstractLogEntry {

    /**
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $action
     *
     * @ORM\Column(length=8)
     */
    protected $action;

    /**
     * @var string $loggedAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $loggedAt;

    /**
     * @var string $objectId
     *
     * @ORM\Column(length=64, nullable=true)
     */
    protected $objectId;

    /**
     * @var string $objectClass
     *
     * @ORM\Column(length=255)
     */
    protected $objectClass;

    /**
     * @var integer $version
     *
     * @ORM\Column(type="integer")
     */
    protected $version;

    /**
     * @var string $data
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $data;

    /**
     * @var string $data
     *
     * @ORM\Column(length=255, nullable=true)
     */
    protected $username;

    /**
     * @var string $createdFromIp
     *
     * @Gedmo\IpTraceable(on="create")
     * @ORM\Column(length=45, nullable=true)
     */
    private $createdFromIp;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set action
     *
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * Get object class
     *
     * @return string
     */
    public function getObjectClass() {
        return $this->objectClass;
    }

    /**
     * Set object class
     *
     * @param string $objectClass
     */
    public function setObjectClass($objectClass) {
        $this->objectClass = $objectClass;
    }

    /**
     * Get object id
     *
     * @return string
     */
    public function getObjectId() {
        return $this->objectId;
    }

    /**
     * Set object id
     *
     * @param string $objectId
     */
    public function setObjectId($objectId) {
        $this->objectId = $objectId;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Get loggedAt
     *
     * @return \DateTime
     */
    public function getLoggedAt() {
        return $this->loggedAt;
    }

    /**
     * Set loggedAt to "now"
     */
    public function setLoggedAt() {
        $this->loggedAt = new \DateTime();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Set current version
     *
     * @param integer $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * Get current version
     *
     * @return integer
     */
    public function getVersion() {
        return $this->version;
    }

    public function getCreatedFromIp() {
        return $this->createdFromIp;
    }

   

}