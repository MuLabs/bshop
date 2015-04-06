<?php
namespace Mu\Bundle\Bshop\Model\Entity;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Attribute extends Kernel\Model\Entity
{
    private $name;
    private $nameRewritten;
    private $type;
    private $dateInsert;
    private $dateEdit;

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameRewritten() {
        return $this->nameRewritten;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    public function getTypeDisplay() {
        return $this->getManager()->type2Display($this->type);
    }

    /**
     * @return int
     */
    public function getDateInsert() {
        return $this->dateInsert;
    }

    /**
     * @return int
     */
    public function getDateEdit() {
        return $this->dateEdit;
    }


    /**
     * @param string $dateEdit
     */
    public function setDateEdit($dateEdit)
    {
        $this->dateEdit = $dateEdit;
        $this->setProperty('dateEdit', $this->dateEdit);
    }

    /**
     * @param string $dateInsert
     */
    public function setDateInsert($dateInsert)
    {
        $this->dateInsert = $dateInsert;
        $this->setProperty('dateInsert', $this->dateInsert);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setProperty('name', $this->name);
    }

    /**
     * @param string $nameRewritten
     */
    public function setNameRewritten($nameRewritten)
    {
        $this->nameRewritten = $nameRewritten;
        $this->setProperty('nameRewritten', $this->nameRewritten);
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = (int)$type;
        $this->setProperty('type', $this->type);
    }

    /**
     * @return array|string
     */
    public function jsonSerialize()
    {
        return array(
            'idAttribute' => $this->getId(),
            'name' => $this->getName(),
            'nameRewritten' => $this->getNameRewritten(),
            'type' => $this->getType(),
            'dateInsert' => $this->getDateInsert(),
            'dateEdit' => $this->getDateEdit(),
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->getName();
    }

    /**
     * @return Bundle\Bshop\Model\Manager\Attribute
     */
    public function getManager() {
        return parent::getManager();
    }
}