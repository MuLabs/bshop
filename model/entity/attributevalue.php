<?php
namespace Mu\Bundle\Bshop\Model\Entity;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class AttributeValue extends Kernel\Model\Entity
{
    private $idAttribute;
    private $value;
    private $active;
    private $dateInsert;
    private $dateEdit;

    /**
     * @return int
     */
    public function getIdAttribute() {
        return $this->idAttribute;
    }

    /**
     * @return Attribute
     */
    public function getAttribute() {

        if (!isset($this->_cache[__FUNCTION__])) {
            $this->_cache[__FUNCTION__] = $this->getApp()->getAttributeManager()->get($this->getIdAttribute());
        }

        return $this->_cache[__FUNCTION__];
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->active;
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
     * @deprecated
     * @param int $idAttribute
     */
    public function setIdAttribute($idAttribute)
    {
        $this->idAttribute = (int)$idAttribute;
        $this->setProperty('idAttribute', $this->idAttribute);
    }

    /**
     * @param Attribute $attribute
     */
    public function setAttribute(Attribute $attribute) {
        $this->setIdAttribute($attribute->getId());
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->setProperty('value', $this->value);
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;
        $this->setProperty('active', $this->active);
    }

    /**
     * @return array|string
     */
    public function jsonSerialize()
    {
        return array(
            'idAttributeValue' => $this->getId(),
            'idAttribute' => $this->getIdAttribute(),
            'value' => $this->getValue(),
            'active' => $this->isActive(),
            'dateInsert' => $this->getDateInsert(),
            'dateEdit' => $this->getDateEdit(),
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->getValue();
    }

    /**
     * @return Bundle\Bshop\Model\Manager\AttributeValue
     */
    public function getManager() {
        return parent::getManager();
    }
}