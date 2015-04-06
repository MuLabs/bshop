<?php
namespace Mu\Bundle\Bshop\Model\Manager;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class AttributeValue extends Kernel\Model\Manager
{
    protected $properties = array(
        'attributeValue' => array(
            'infos' => array(
                'db' => 'attributeValue'
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
                    'title' => 'ID AttributeValue',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'idAttributeValue',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'int',
                    ),
                ),
                'idAttribute' => array(
                    'title' => 'ID Attribute',
                    'form' => array(
                        'type' => 'select',
                        'required' => 'required'
                    ),
                    'database' => array(
                        'attribute' => 'idAttribute',
                        'pdo_extra' => 'UNSIGNED NOT NULL',
                        'type' => 'smallint',
                    ),
                ),
                'value' => array(
                    'title' => 'AttributeValue value',
                    'form' => array(
                        'type' => 'input',
                    ),
                    'database' => array(
                        'attribute' => 'value',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 50,
                    ),
                ),
                'active' => array(
                    'title' => 'Is attributeValue active',
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
                    'title' => 'Is attribute deleted',
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
    );

    /**
     * @param int[] $ids
     * @return Kernel\Model\Entity[]
     */
    protected function initEntities(array $ids)
    {
        $where = implode(', ', array_fill(0, count($ids), '?'));
        $dbh = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id, :idAttribute, :value, :active, :dateInsert, :dateEdit
				FROM @
			    WHERE :id IN (' . $where . ')';
        $query = new Kernel\Db\Query($sql, $ids, $this);

        $result = $dbh->sendQuery($query);
        $entities = array();
        while (list($id, $idAttribute, $value, $active, $dateInsert, $dateEdit) = $result->fetchRow()) {
            /** @var Bundle\Bshop\Model\Entity\AttributeValue $entity */
            $entity = $this->generateEntity($id);

            if (!$entity) {
                continue;
            }

            $entity->setId($id);
            $entity->setIdAttribute($idAttribute);
            $entity->setValue($value);
            $entity->setActive($active);
            $entity->setDateInsert($dateInsert);
            $entity->setDateEdit($dateEdit);
            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @param array $parameters
     * @return Bundle\Bshop\Model\Entity\AttributeValue
     * @throws \Mu\Kernel\Model\Exception
     */
    public function create(array $parameters = array())
    {
        $invalid = array();
        if (empty($parameters['attribute'])) {
            $invalid[] = 'attribute';
        }
        /** @var Bundle\Bshop\Model\Entity\Attribute $attribute */
        $attribute = $parameters['attribute'];
        if ($attribute->getEntityType() !== $this->getApp()->getConstant('ENTITY_ATTRIBUTE')) {
            $invalid[] = 'attribute';
        }

        if (!isset($parameters['value'])) {
            $invalid[] = 'value';
        }

        if (count($invalid)) {
            throw new Kernel\Model\Exception(implode(
                ', ',
                $invalid
            ), Kernel\Model\Exception::INVALID_CREATE_PARAMETERS);
        }

        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $sql = 'INSERT INTO @ (:idAttribute, :value, :dateInsert) VALUES (?, ?, NOW())';

        $query = new Kernel\Db\Query($sql, array(
            $attribute->getId(),
            $parameters['value'],
        ), $this);
        $handler->sendQuery($query);
        $idAttributeValue = $handler->getInsertId();
        $attributeValue = $this->get($idAttributeValue);

        $attributeValue->logAction(
            Kernel\Backoffice\ActionLogger::ACTION_CREATE,
            array(),
            $parameters
        );
        $this->discard();

        return $attributeValue;
    }

    /**
     * @param bool $activeOnly
     * @param bool $notDeletedOnly
     * @return Bundle\Bshop\Model\Entity\AttributeValue[]
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
     * @param mixed $id
     * @return Bundle\Bshop\Model\Entity\AttributeValue
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

        $attribute = $this->getApp()->getAttributeManager()->getByName('marque');
        $toCreate = array(
            array(
                'attribute' => $attribute,
                'value' => 'Nike',
            ),
            array(
                'attribute' => $attribute,
                'value' => 'Adidas',
            ),
            array(
                'attribute' => $attribute,
                'value' => 'Puma',
            ),
        );

        foreach ($toCreate as $one) {
            $this->create($one);
        }

        return true;
    }
}