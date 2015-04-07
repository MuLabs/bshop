<?php
namespace Mu\Bundle\Bshop\Model\Manager;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Product extends Kernel\Model\Manager
{
    protected $properties = array(
        'product' => array(
            'infos' => array(
                'db' => 'product'
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'id',
                    ),
                ),
            ),
            'properties' => array(
                'id' => array(
                    'title' => 'ID Product',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'idProduct',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'int',
                    ),
                ),
                'idCategory' => array(
                    'title' => 'ID Category',
                    'form' => array(
                        'type' => 'select',
                        'required' => 'required'
                    ),
                    'database' => array(
                        'attribute' => 'idCategory',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
                    ),
                ),
                'name' => array(
                    'title' => 'Product name',
                    'form' => array(
                        'type' => 'input',
                        'required' => true
                    ),
                    'database' => array(
                        'attribute' => 'name',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 50,
                    ),
                ),
                'nameRewritten' => array(
                    'title' => 'Product name rewritten',
                    'form' => array(
                        'type' => 'input',
                    ),
                    'database' => array(
                        'attribute' => 'nameRewritten',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 50,
                    ),
                ),
                'description' => array(
                    'title' => 'Product full description',
                    'form' => array(
                        'type' => 'textarea'
                    ),
                    'database' => array(
                        'attribute' => 'description',
                        'pdo_extra' => 'NOT NULL DEFAULT ""',
                        'type' => 'text',
                    ),
                ),

                'descriptionShort' => array(
                    'title' => 'Product short description',
                    'form' => array(
                        'type' => 'input',
                    ),
                    'database' => array(
                        'attribute' => 'descriptionShort',
                        'pdo_extra' => 'NOT NULL DEFAULT ""',
                        'type' => 'varchar',
                        'length' => 100,
                    ),
                ),
                'display' => array(
                    'title' => 'Is product displayed',
                    'form' => array(
                        'type' => 'checkbox',
                    ),
                    'database' => array(
                        'attribute' => 'displayed',
                        'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 1',
                        'type' => 'tinyint',
                    ),
                ),
                'new' => array(
                    'title' => 'Is product new',
                    'form' => array(
                        'type' => 'checkbox',
                    ),
                    'database' => array(
                        'attribute' => 'new',
                        'pdo_extra' => 'UNSIGNED NOT NULL DEFAULT 1',
                        'type' => 'tinyint',
                    ),
                ),
                'deleted' => array(
                    'title' => 'Is product deleted',
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
        'attributeValue' => array(
            'infos' => array(
                'db' => 'productAttributeValue'
            ),
            'keys' => array(
                'pk_id' => array(
                    'type' => 'primary',
                    'properties' => array(
                        'idProduct',
                        'idAttributeValue',
                    ),
                ),
            ),
            'properties' => array(
                'idProduct' => array(
                    'title' => 'ID Product',
                    'database' => array(
                        'attribute' => 'idProduct',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'int',
                    ),
                ),
                'idAttributeValue' => array(
                    'title' => 'ID AttributeValue',
                    'database' => array(
                        'attribute' => 'idAttribute',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
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
        $sql = 'SELECT :id, :idCategory, :name, :nameRewritten, :description, :descriptionShort, :display, :new, :dateInsert, :dateEdit
				FROM @
			    WHERE :id IN (' . $where . ')';
        $query = new Kernel\Db\Query($sql, $ids, $this);

        $result = $dbh->sendQuery($query);
        $entities = array();
        while (list($id, $idCategory, $name, $nameRewritten, $description, $descriptionShort, $display, $new, $dateInsert, $dateEdit) = $result->fetchRow()) {
            /** @var Bundle\Bshop\Model\Entity\Product $entity */
            $entity = $this->generateEntity($id);

            if (!$entity) {
                continue;
            }

            $entity->setId($id);
            $entity->setIdCategory($idCategory);
            $entity->setName($name);
            $entity->setNameRewritten($nameRewritten);
            $entity->setDescription($description);
            $entity->setDescriptionShort($descriptionShort);
            $entity->setDisplay($display);
            $entity->setNew($new);
            $entity->setDateInsert($dateInsert);
            $entity->setDateEdit($dateEdit);
            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @param array $parameters
     * @return Bundle\Bshop\Model\Entity\Product
     * @throws \Mu\Kernel\Model\Exception
     */
    public function create(array $parameters = array())
    {
        $invalid = array();
        if (empty($parameters['category'])) {
            $invalid[] = 'category';
        }
        /** @var Bundle\Bshop\Model\Entity\Category $category */
        $category = $parameters['category'];
        if ($category->getEntityType() !== $this->getApp()->getConstant('ENTITY_CATEGORY')) {
            $invalid[] = 'category';
        }

        if (empty($parameters['name'])) {
            $invalid[] = 'name';
        }
        if (empty($parameters['nameRewritten'])) {
            $parameters['nameRewritten'] = $this->getApp()->getToolbox()->rewriteString($parameters['name']);
        }

        if (count($invalid)) {
            throw new Kernel\Model\Exception(implode(
                ', ',
                $invalid
            ), Kernel\Model\Exception::INVALID_CREATE_PARAMETERS);
        }

        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $sql = 'INSERT INTO @ (:idCategory, :name, :nameRewritten, :description, :descriptionShort, :display, :new, :dateInsert) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';

        $query = new Kernel\Db\Query($sql, array(
            $category->getId(),
            $parameters['name'],
            $parameters['nameRewritten'],
            isset($parameters['description']) ? $parameters['description'] : '',
            isset($parameters['descriptionShort']) ? $parameters['descriptionShort'] : '',
            isset($parameters['display']) ? $parameters['display'] : true,
            isset($parameters['new']) ? $parameters['new'] : true,
        ), $this);
        $handler->sendQuery($query);
        $idProduct = $handler->getInsertId();
        $product   = $this->get($idProduct);

        $product->logAction(
            Kernel\Backoffice\ActionLogger::ACTION_CREATE,
            array(),
            $parameters
        );
        $this->discard();

        return $product;
    }

    /**
     * @param bool $activeOnly
     * @param bool $notDeletedOnly
     * @return Bundle\Bshop\Model\Entity\Product[]
     */
    public function getAll($activeOnly = true, $notDeletedOnly = false) {
        $key = __FUNCTION__.'|'.$activeOnly.'|'.$notDeletedOnly;
        if (!isset($this->_cache[$key])) {
            $where = array();
            $whereVal = array();

            if (!$notDeletedOnly) {
                $where[] = ':deleted = ?';
                $whereVal[] = 0;
            }

            if ($activeOnly) {
                $where[] = ':active = ?';
                $whereVal[] = 1;
            }

            $where = implode(' AND ', $where);
            if (!empty($where)) {
                $where = ' WHERE '.$where;
            }

            $handler = $this->getApp()->getDatabase()->getHandler('readFront');
            $sql = 'SELECT :id
                      FROM @ '.$where;

            $query = new Kernel\Db\Query($sql, $whereVal, $this);
            $result = $handler->sendQuery($query);
            $this->_cache[__FUNCTION__] = $result->fetchAllValue();
        }

        return $this->multiGet($this->_cache[__FUNCTION__]);
    }

    /**
     * @param Bundle\Bshop\Model\Entity\Category $category
     * @return Bundle\Bshop\Model\Entity\Product[]
     */
    public function getFromCategory(Bundle\Bshop\Model\Entity\Category $category) {
        $where = array(':idCategory = ?');
        $whereVal = array($category->getId());

        $where = implode(' AND ', $where);
        if (!empty($where)) {
            $where = ' WHERE '.$where;
        }

        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id
                  FROM @ '.$where;

        $query = new Kernel\Db\Query($sql, $whereVal, $this);
        $result = $handler->sendQuery($query);
        return $this->multiGet($result->fetchAllValue());
    }

    /**
     * @param mixed $id
     * @return Bundle\Bshop\Model\Entity\Product
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
        $categoryList = $this->getApp()->getCategoryManager()->getAll();
        $categoryCount = count($categoryList);

        $limit = 50;
        for ($i = 0; $i < $limit; ++$i) {
            $product = $this->create(array(
                'category' => $categoryList[rand(1, $categoryCount)],
                'name' => 'product'.rand(0, 10000),
            ));

            $attrList = $product->getCategory()->getAllAttributes();
            foreach ($attrList as $oneAttribute) {
                $attrValues = $oneAttribute->getAvailableValues();
                shuffle($attrValues);
                $countAttrValues = count($attrValues);
                if ($countAttrValues) {
                    $product->addAttributeValue($attrValues[rand(0, $countAttrValues-1)]);
                }
            }
        }
    }
}