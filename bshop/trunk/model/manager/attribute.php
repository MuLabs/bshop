<?php
namespace Mu\Bundle\Bshop\Model\Manager;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

class Attribute extends Kernel\Model\Manager
{
    const TYPE_STRING 	= 1;
    const TYPE_INT 		= 2;
    const TYPE_COLOR	= 3;

    protected $properties = array(
        'attribute' => array(
            'infos' => array(
                'db' => 'attribute'
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
                    'title' => 'ID Attribute',
                    'form' => array(
                        'type' => 'hidden',
                    ),
                    'database' => array(
                        'attribute' => 'idAttribute',
                        'pdo_extra' => 'UNSIGNED NOT NULL AUTO_INCREMENT',
                        'type' => 'smallint',
                    ),
                ),
                'name' => array(
                    'title' => 'Attribute name',
                    'form' => array(
                        'type' => 'input',
                        'required' => 'required'
                    ),
                    'database' => array(
                        'attribute' => 'name',
                        'pdo_extra' => 'NOT NULL',
                        'type' => 'varchar',
                        'length' => 50,
                    ),
                ),
                'nameRewritten' => array(
                    'title' => 'Attribute name rewritten',
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
                'type' => array(
                    'title' => 'Attribute type',
                    'form' => array(
                        'type' => 'select',
                    ),
                    'database' => array(
                        'attribute' => 'type',
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
        $sql = 'SELECT :id, :name, :nameRewritten, :type, :dateInsert, :dateEdit
				FROM @
			    WHERE :id IN (' . $where . ')';
        $query = new Kernel\Db\Query($sql, $ids, $this);

        $result = $dbh->sendQuery($query);
        $entities = array();
        while (list($id, $name, $nameRewritten, $type, $dateInsert, $dateEdit) = $result->fetchRow()) {
            /** @var Bundle\Bshop\Model\Entity\Attribute $entity */
            $entity = $this->generateEntity($id);

            if (!$entity) {
                continue;
            }

            $entity->setId($id);
            $entity->setName($name);
            $entity->setNameRewritten($nameRewritten);
            $entity->setType($type);
            $entity->setDateInsert($dateInsert);
            $entity->setDateEdit($dateEdit);
            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @param array $parameters
     * @return Bundle\Bshop\Model\Entity\Attribute
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
        if (empty($parameters['type'])) {
            $invalid[] = 'type';
        }

        if (count($invalid)) {
            throw new Kernel\Model\Exception(implode(
                ', ',
                $invalid
            ), Kernel\Model\Exception::INVALID_CREATE_PARAMETERS);
        }

        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');
        $sql = 'INSERT INTO @ (:name, :nameRewritten, :type, :dateInsert) VALUES (?, ?, ?, NOW())';

        $query = new Kernel\Db\Query($sql, array(
            $parameters['name'],
            $parameters['nameRewritten'],
            $parameters['type'],
        ), $this);
        $handler->sendQuery($query);
        $idAttribute = $handler->getInsertId();
        $attribute   = $this->get($idAttribute);

        $attribute->logAction(
            Kernel\Backoffice\ActionLogger::ACTION_CREATE,
            array(),
            $parameters
        );
        $this->discard();

        return $attribute;
    }

    /**
     * @param  $name
     * @return Bundle\Bshop\Model\Entity\Attribute
     */
    public function getFromNameRewritten($name) {
        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id
				FROM @
				WHERE :nameRewritten = ?
				LIMIT 1;';

        $query = new Kernel\Db\Query($sql, array($name), $this);
        $result = $handler->sendQuery($query);

        list($idCategory) = $result->fetchRow();
        return $this->get($idCategory);
    }

    /**
     *
     * @param string $name
     * @return Bundle\Bshop\Model\Entity\Attribute[]
     */
    public function getByName($name) {
        $where[] = 'name = ?';
        $whereVal[] = $name;
        $where = implode(' AND ', $where);

        $handler = $this->getApp()->getDatabase()->getHandler('readFront');
        $sql = 'SELECT :id
                  FROM @
                  WHERE '.$where.'
                  LIMIT 1;';

        $query = new Kernel\Db\Query($sql, $whereVal, $this);
        $result = $handler->sendQuery($query);

        return $this->get($result->fetchValue());
    }

    /**
     *
     * @param bool $notDeletedOnly
     * @return Bundle\Bshop\Model\Entity\Attribute[]
     */
    public function getAll($notDeletedOnly = false) {
        $key = __FUNCTION__.'|'.$notDeletedOnly;
        if (!isset($this->_cache[$key])) {
            $where = array();
            $whereVal = array();

            if (!$notDeletedOnly) {
                $where[] = ':deleted = ?';
                $whereVal[] = 0;
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
     * @return array
     */
    public function getTypeList() {
        return array(
            self::TYPE_STRING	=> 'Chaine de caractère',
            self::TYPE_INT 		=> 'Nombre',
            self::TYPE_COLOR 	=> 'Couleur',
        );
    }

    /**
     * @param int $type
     * @return string
     */
    public function type2Display($type) {
        $type_list = $this->getTypeList();
        return (isset($type_list[$type])) ? $type_list[$type] : '';
    }

    /**
     * @param mixed $id
     * @return Bundle\Bshop\Model\Entity\Attribute
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

        $toCreate = array(
            array(
                'name' => 'marque',
                'nameRewritten' => 'marque',
                'type' => self::TYPE_STRING,
            )
        );

        foreach ($toCreate as $one) {
            $this->create($one);
        }

        return true;
    }
}