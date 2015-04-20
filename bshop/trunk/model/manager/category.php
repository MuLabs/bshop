<?php
namespace Mu\Bundle\Bshop\Model\Manager;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Category extends Kernel\Model\Manager
{
    protected $properties = array(
        'category' => array(
            'infos' => array(
                'db' => 'category'
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'id',
                    ),
                ),
                'index_nameRewritten' => array(
                    'type' => 'index',
                    'properties' => array(
                        'nameRewritten',
                    ),
                ),
                'index_active_idParent' => array(
                    'type' => 'index',
                    'properties' => array(
                        'active',
                        'idParent',
                    ),
                ),
            ),
            'properties' => array(
                'id' => array(
                    'title' => 'ID Category',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'idCategory',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'smallint',
                    ),
                ),
                'idParent' => array(
                    'title' => 'Category parent',
                    'form' => array(
                        'type' => 'input',
                    ),
                    'database' => array(
                        'attribute' => 'idParent',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
                    ),
                ),
                'name' => array(
                    'title' => 'Category name',
                    'form' => array(
                        'type' => 'input',
                        'required' => 'required'
                    ),
                    'database' => array(
                        'attribute' => 'name',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 100,
                    ),
                ),
                'nameRewritten' => array(
                    'title' => 'Category name rewritten',
                    'form' => array(
                        'type' => 'input',
                    ),
                    'database' => array(
                        'attribute' => 'nameRewritten',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 100,
                    ),
                ),
                'description' => array(
                    'title' => 'Category description',
                    'form' => array(
                        'type' => 'textarea',
                    ),
                    'database' => array(
                        'attribute' => 'description',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'text',
                    ),
                ),
                'active' => array(
                    'title' => 'Category active',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'active',
                        'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 1',
                        'type' => 'tinyint',
                    ),
                ),
                'deleted' => array(
                    'title' => 'Category deleted',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'deleted',
                        'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 0',
                        'type' => 'tinyint',
                    ),
                ),
                'dateInsert' => array(
                    'title' => 'Date d\'instertion',
                    'form' => array(
                        'type' => 'date',
                    ),
                    'database' => array(
                        'attribute' => 'dateInsert',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'date'
                    ),
                ),
                'dateEdit' => array(
                    'title' => 'Date d\'édition',
                    'form' => array(
                        'type' => 'date',
                    ),
                    'database' => array(
                        'attribute' => 'dateEdit',
                        'pdo_extra' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                        'type' => 'timestamp',
                    ),
                ),
            )
        ),
        'attribute' => array(
            'infos' => array(
                'db' => 'categoryAttribute'
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'idCategory',
                        'idAttribute',
                    ),
                ),
            ),
            'properties' => array(
                'idCategory' => array(
                    'title' => 'ID Category',
                    'database' => array(
                        'attribute' => 'idCategory',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
                    ),
                ),
                'idAttribute' => array(
                    'title' => 'ID Attribute',
                    'database' => array(
                        'attribute' => 'idAttribute',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
                    ),
                ),
                'filterable' => array(
                    'title' => 'Is attribute filterable in this section',
                    'database' => array(
                        'attribute' => 'filterable',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'tinyint',
                    ),
                ),
                'discriminating' => array(
                    'title' => 'Is attribute discrimination for this category',
                    'database' => array(
                        'attribute' => 'discriminating',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'tinyint',
                    ),
                ),
            )
        )
    );

    /**
     * @param int[] $ids
     * @return Kernel\Model\Entity[]
     */
    protected function initEntities(array $ids)
    {
        $where = implode(', ', array_fill(0, count($ids), '?'));
        $dbh = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id, :idParent, :name, :nameRewritten, :description, :active, :dateInsert, :dateEdit
				FROM @
			    WHERE :id IN (' . $where . ')';
        $query = new Kernel\Db\Query($sql, $ids, $this);

        $result = $dbh->sendQuery($query);
        $entities = array();
        while (list($id, $idParent, $name, $nameRewritten, $description, $active, $dateInsert, $dateEdit) = $result->fetchRow()) {
            /** @var Bundle\Bshop\Model\Entity\Category $entity */
            $entity = $this->generateEntity($id);

            if (!$entity) {
                continue;
            }

            $entity->setId($id);
            $entity->setIdParent($idParent);
            $entity->setName($name);
            $entity->setNameRewritten($nameRewritten);
            $entity->setDescription($description);
            $entity->setDateInsert($dateInsert);
            $entity->setDateEdit($dateEdit);
            $entity->setActive($active);
            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @param array $parameters
     * @return Bundle\Bshop\Model\Entity\Category
     * @throws \Mu\Kernel\Model\Exception
     */
    public function create(array $parameters = array())
    {
        $invalid = array();
        if (empty($parameters['name'])) {
            $invalid[] = 'name';
        }
        if (empty($parameters['nameRewritten'])) {
            $parameters['nameRewritten'] = $this->getApp()->getToolbox()->rewriteString($parameters['name']);
        }
        if (empty($parameters['parent']) || !($parameters['parent'] instanceof Bundle\Bshop\Model\Entity\Category)) {
            $idParent = 0;
        } else {
            /** @var Bundle\Bshop\Model\Entity\Category $parent */
            $parent   = $parameters['parent'];
            $idParent = $parent->getId();
        }

        if (count($invalid)) {
            throw new Kernel\Model\Exception(implode(
                ', ',
                $invalid
            ), Kernel\Model\Exception::INVALID_CREATE_PARAMETERS);
        }

        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $sql = 'INSERT INTO @ (:name, :nameRewritten, :idParent, :description, :dateInsert) VALUES (?, ?, ?, ?, NOW())';

        $query = new Kernel\Db\Query($sql, array(
            $parameters['name'],
            $parameters['nameRewritten'],
            $idParent,
            (isset($parameters['description'])) ? $parameters['description'] : '',
        ), $this);
        $handler->sendQuery($query);
        $idCategory = $handler->getInsertId();
        $category   = $this->get($idCategory);

        $category->logAction(
            Kernel\Backoffice\ActionLogger::ACTION_CREATE,
            array(),
            $parameters
        );
        $this->discard();

        return $category;
    }

    /**
     * @param $name
     * @return Bundle\Bshop\Model\Entity\Category
     */
    public function getFromName($name)
    {
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id
				FROM @
				WHERE :name = ?
				LIMIT 1;';

        $query = new Kernel\Db\Query($sql, array($name), $this);
        $result = $handler->sendQuery($query);

        list($idCategory) = $result->fetchRow();

        return $this->get($idCategory);
    }

    /**
     * @param $nameRewritten
     * @return Bundle\Bshop\Model\Entity\Category
     */
    public function getFromNameRewritten($nameRewritten)
    {
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id
				FROM @
				WHERE :nameRewritten = ?
				AND :deleted = 0
				LIMIT 1;';
        $query = new Kernel\Db\Query($sql, array($nameRewritten), $this);
        $result = $handler->sendQuery($query);

        list($idCategory) = $result->fetchRow();

        return $this->get($idCategory);
    }

    /**
     * @param Bundle\Bshop\Model\Entity\Category $parent
     * @param bool $onlyActive
     * @param string $orderBy
     * @return Bundle\Bshop\Model\Entity\Category[]
     */
    public function getCategoriesList(
        Bundle\Bshop\Model\Entity\Category $parent = null,
        $onlyActive = true,
        $orderBy = null
    ) {
        $idParent = ($parent) ? $parent->getId() : 0;
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');

        $whereCol = array(':idParent = ?', ':deleted = ?');
        $whereVal = array($idParent, 0);

        if ($onlyActive) {
            $whereCol[] = ':active = ?';
            $whereVal[] = 1;
        }

        switch ($orderBy) {
            case 'name':
                $orderBy = ' ORDER BY :name';
                break;
            default:
                $orderBy = '';
                break;
        }

        $whereCol = implode(' AND ', $whereCol);

        $sql = 'SELECT :id
				FROM @
				WHERE ' . $whereCol . $orderBy;

        $query = new Kernel\Db\Query($sql, $whereVal, $this);
        $result = $handler->sendQuery($query);

        return $this->multiGet($result->fetchAllValue());
    }

    /**
     * @param bool $active
     * @return Bundle\Bshop\Model\Entity\Category[]
     */
    public function getAll($active = true)
    {
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');

        $whereCol = array(':deleted = ?',':active = ?');
        $whereVal = array(0);
        $whereVal[] = ( $active ) ? 1 : 0;

        $whereCol = implode(' AND ', $whereCol);

        $sql = 'SELECT :id
				FROM @
				WHERE ' . $whereCol . ';';

        $query = new Kernel\Db\Query($sql, $whereVal, $this);
        $result = $handler->sendQuery($query);

        return $this->multiGet($result->fetchAllValue());
    }

    /**
     * @param Bundle\Bshop\Model\Entity\Category $parent
     * @param array $arbo
     * @param int $level
     * @return array()
     */
    public function getFullCategoryArbo(Bundle\Bshop\Model\Entity\Category $parent = null, $level = 0, $arbo = array() )
    {
        $idParent = ($parent) ? $parent->getId() : 0;
        $key = 'categories|arbo|' . $idParent . '|' . $level;

        if (empty($this->_cache[$key])) {
            $categories = $this->getCategoriesList($parent);
            ++$level;

            foreach ($categories as $category) {
                $arbo[] = array(
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'parent' => $idParent,
                    'nameRewritten' => $category->getNameRewritten(),
                    'level' => $level,
                );

                $arbo = $this->getFullCategoryArbo($category,  $level, $arbo);
            }

            $this->_cache[$key] = $arbo;
        }

        return $this->_cache[$key];
    }

    /**
     * @param Bundle\Bshop\Model\Entity\Category $category
     * @return Bundle\Bshop\Model\Entity\Attribute[]
     */
    public function getAttributes(Bundle\Bshop\Model\Entity\Category $category) {
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT attribute.idAttribute
                  FROM @attribute
                  WHERE :attribute.idCategory = ?;';

        $query = new Kernel\Db\Query($sql, array($category->getId()), $this);
        $result = $handler->sendQuery($query);

        return $this->multiGet($result->fetchAllValue());
    }


    /**
     * @param mixed $id
     * @return Bundle\Bshop\Model\Entity\Category
     */
    public function get($id)
    {
        return parent::get($id);
    }

    /**
     * @param string $stdOut
     * @return bool
     */
    public function createDefaultDataSet($stdOut = '\print')
    {
        $className = $this->getEntityClassname();
        call_user_func($stdOut, $className . ' => Generating Datas');

        $parentCat = $this->create(array(
            'name' => 'Vêtements',
        ));
        $attrBrand = $this->getApp()->getAttributeManager()->getFromName('marque');
        $attrColor = $this->getApp()->getAttributeManager()->getFromName('couleur');
        $toCreate = array(
            array(
                'parent' => $parentCat,
                'name' => 'Chaussures',
            ),
            array(
                'parent' => $parentCat,
                'name' => 'T-Shirts',
            ),
            array(
                'parent' => $parentCat,
                'name' => 'Pantalons',
            ),
        );

        foreach ($toCreate as $one) {
            $category = $this->create($one);
            $category->addAttribute($attrBrand, true, false);
            $category->addAttribute($attrColor, true, false);
        }

        return true;
    }
}