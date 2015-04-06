<?php
namespace Mu\Bundle\Bshop\Model\Entity;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Product extends Kernel\Model\Entity
{
    private $idCategory;
    private $name;
    private $nameRewritten;
    private $description;
    private $descriptionShort;
    private $display;
    private $new;
    private $dateInsert;
    private $dateEdit;

    /**
     * @return int
     */
    public function getIdCategory() {
        return $this->idCategory;
    }

    /**
     * @return Category
     */
    public function getCategory() {
        if (!isset($this->_cache[__FUNCTION__])) {
            $this->_cache[__FUNCTION__] = $this->getApp()->getCategoryManager()->get($this->getIdCategory());
        }

        return $this->_cache[__FUNCTION__];
    }

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
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDescriptionShort() {
        return $this->descriptionShort;
    }

    /**
     * @return bool
     */
    public function isDisplayed() {
        return $this->display;
    }

    /**
     * @return bool
     */
    public function isNew() {
        return $this->new;
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
     * @param int $idCategory
     */
    public function setIdCategory($idCategory)
    {
        $this->idCategory = (int)$idCategory;
        $this->setProperty('idCategory', $this->idCategory);
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category) {
        $this->setIdCategory($category->getId());
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
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->setProperty('description', $this->description);
    }

    /**
     * @param string $descriptionShort
     */
    public function setDescriptionShort($descriptionShort)
    {
        $this->descriptionShort = $descriptionShort;
        $this->setProperty('descriptionShort', $this->descriptionShort);
    }

    /**
     * @param bool $display
     */
    public function setDisplay($display)
    {
        $this->display = (bool)$display;
        $this->setProperty('display', $this->display);
    }

    /**
     * @param bool $new
     */
    public function setNew($new)
    {
        $this->new = (bool)$new;
        $this->setProperty('new', $this->new);
    }

    /**
     * @return array|string
     */
    public function jsonSerialize()
    {
        return array(
            'idProduct' => $this->getId(),
            'idCategory' => $this->getIdCategory(),
            'name' => $this->getName(),
            'nameRewritten' => $this->getNameRewritten(),
            'description' => $this->getDescription(),
            'descriptionShort' => $this->getDescriptionShort(),
            'display' => $this->isDisplayed(),
            'new' => $this->isNew(),
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
     * @return Bundle\Bshop\Model\Manager\Product
     */
    public function getManager() {
        return parent::getManager();
    }
}