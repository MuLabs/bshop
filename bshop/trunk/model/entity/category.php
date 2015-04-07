<?php
namespace Mu\Bundle\Bshop\Model\Entity;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Category extends Kernel\Model\Entity
{
    protected $idParent;
    protected $name;
    protected $nameRewritten;
    protected $description;
    protected $dateInsert;
    protected $dateEdit;
    protected $active;

    #region Getters
    /**
     * @return int
     */
    public function getIdParent()
    {
        return $this->idParent;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameRewritten()
    {
        return $this->nameRewritten;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDateInsert()
    {
        return $this->dateInsert;
    }

    /**
     * @return string
     */
    public function getDateEdit()
    {
        return $this->dateEdit;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    #endregion

    #region Setters
    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;
        $this->setProperty('active', $this->active);
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
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->setProperty('description', $this->description);
    }

    /**
     * @internal
     * @param int $idParent
     */
    public function setIdParent($idParent)
    {
        $this->idParent = (int)$idParent;
        $this->setProperty('idParent', $this->idParent);
    }

    /**
     * @param Category $parent
     */
    public function setParent(Category $parent = null)
    {
        $idParent = $parent ? $parent->getId() : 0;
        $this->setIdParent($idParent);
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
    #endregion

    #region Getters specific
    /**
     * @return Bundle\Bshop\Model\Entity\Category
     */
    public function getParent()
    {
        return $this->getManager()->get($this->getIdParent());
    }

    /**
     * @return Bundle\Bshop\Model\Manager\Category
     */
    public function getManager()
    {
        return parent::getManager();
    }

    #endregion

    /**
     * @return Category[]
     */
    public function getSubcategoriesRecursive()
    {
        $key = 'categories|subcats|' . $this->getId();

        if (empty($this->_cache[$key])) {
            $catList = array();
            $arbo = $this->getManager()->getFullCategoryArbo($this);
            foreach ($arbo as $cat) {
                $category = $this->getManager()->get($cat['id']);

                if ($category) {
                    $catList[] = $category;
                }
            }

            $this->_cache[$key] = $catList;
        }

        return $this->_cache[$key];
    }

    /**
     * @return array
     */
    public function getSubcategories()
    {
        return $this->getManager()->getCategoriesList($this);
    }

    /**
     * @return Bundle\Bshop\Model\Entity\Category
     */
    public function getParentsList()
    {
        if (empty($this->_cache[__FUNCTION__])) {
            $parentsList = array();
            $cCat = $this;

            while ($cCat->getParent()) {
                $parentsList[] = $cCat->getParent();
                $cCat = $cCat->getParent();
            }

            $this->_cache[__FUNCTION__] = array_reverse($parentsList);
        }


        return $this->_cache[__FUNCTION__];
    }

    /**
     * @return bool
     */
    public function hasSubcategories()
    {
        $sql = 'SELECT :idCategory
				FROM @
				WHERE idParent = ?
				LIMIT 1';

        $query = new Kernel\Db\Query($sql, array($this->getId()), $this->getManager());
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $result = $handler->sendQuery($query);

        return (bool)$result->fetchValue();
    }

    /**
     * @return Category[]
     */
    public function getAllProducts() {
        $key = __FUNCTION__;
        if (!isset($this->_cache[$key])) {
            $this->_cache[$key] = $this->getApp()->getProductManager()->getFromCategory($this);
        }

        return $this->_cache[$key];
    }

    /**
     * @return Attribute[]
     */
    public function getAllAttributes() {
        $sql = 'SELECT :attribute.idAttribute
                  FROM @attribute
                  WHERE :attribute.idCategory = ?';

        $query = new Kernel\Db\Query($sql, array(
            $this->getId(),
        ), $this->getManager());
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $result = $handler->sendQuery($query);

        return $this->getApp()->getAttributeManager()->multiGet($result->fetchAllValue());
    }

    /**
     * @param Attribute $attribute
     * @param bool $filterable
     * @param bool $discriminating
     */
    public function addAttribute(Attribute $attribute, $filterable, $discriminating) {
        $sql = 'REPLACE INTO @attribute (:attribute.idAttribute, :attribute.idCategory, :attribute.filterable, :attribute.discriminating)
				VALUES (?, ?, ?, ?)';

        $query = new Kernel\Db\Query($sql, array(
            $attribute->getId(),
            $this->getId(),
            $filterable,
            $discriminating,
        ), $this->getManager());
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $handler->sendQuery($query);
    }

    /**
     * @param Attribute $attribute
     */
    public function removeAttribute(Attribute $attribute) {
        $sql = 'DELETE FROM @attribute
                  WHERE :attribute.idAttribute = ?
                  AND :attribute.idCategory = ?
                  LIMIT 1';

        $query = new Kernel\Db\Query($sql, array(
            $attribute->getId(),
            $this->getId(),
        ), $this->getManager());
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $handler->sendQuery($query);
    }

    /**
     * @return array|string
     */
    public function jsonSerialize()
    {
        return array(
            'idCategory' => $this->getId(),
            'idParent' => $this->getIdParent(),
            'name' => $this->getName(),
            'nameRewritten' => $this->getNameRewritten(),
            'description' => $this->getDescription(),
            'dateInsert' => $this->getDateInsert(),
            'dateEdit' => $this->getDateEdit(),
            'active' => $this->isActive(),
        );
    }

    /**
     * @return string
     */
    function __toString()
    {
        return '' . $this->getName();
    }
}