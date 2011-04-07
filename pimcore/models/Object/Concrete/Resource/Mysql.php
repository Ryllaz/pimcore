<?php
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    Object
 * @copyright  Copyright (c) 2009-2010 elements.at New Media Solutions GmbH (http://www.elements.at)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Object_Concrete_Resource_Mysql extends Object_Abstract_Resource_Mysql {

    /**
     * Contains all valid columns in the database table
     *
     * @var array
     */
    protected $validColumnsObjectConcrete = array();

    /**
     * @var Object_Concrete_Resource_Mysql_InheritanceHelper
     */
    protected $inheritanceHelper = null;

    /**
     * @see Object_Abstract_Resource_Mysql::init  
     */
    public function init() {  
        parent::init();
        $this->inheritanceHelper = new Object_Concrete_Resource_Mysql_InheritanceHelper($this->model->getO_classId());
    }

    /**
     * Get the data for the object from database for the given id
     *
     * @param integer $id
     * @return void
     */
    public function getById($id) {
        try {
            $data = $this->db->fetchRow("SELECT * FROM objects WHERE o_id = ?", $id);

            if ($data["o_id"]) {
                $this->assignVariablesToModel($data);
                $this->getData();
            }
            else {
                throw new Exception("Object with the ID " . $id . " doesn't exists");
            }

        }
        catch (Exception $e) {
            Logger::warning($e);
        }
    }

    /**
     * Get the data for the object from database for the given path
     *
     * @param string $path
     * @return void
     */
    public function getByPath($path) {
        // remove trailing slash if exists
        if (substr($path, -1) == "/" and strlen($path) > 1) {
            $path = substr($path, 0, count($path) - 2);
        }

        $data = $this->db->fetchRow("SELECT * FROM objects WHERE CONCAT(o_path,`o_key`) = '" . $path . "'");

        if ($data["id"]) {
            $this->assignVariablesToModel($data);
            //$this->getData();
        }
        else {
            throw new Exception("object " . $path . " doesn't exist");
        }
    }

    /**
     * @param  string $fieldName
     * @return array
     */
    public function getRelationIds($fieldName) {
        $relations = array();
        $allRelations = $this->db->fetchAll("SELECT * FROM object_relations_" . $this->model->getO_classId() . " WHERE fieldname = ? AND src_id = ? AND ownertype = 'object' ORDER BY `index` ASC", array($fieldName, $this->model->getO_id()));
        foreach ($allRelations as $relation) {
            $relations[] = $relation["dest_id"];
        }
        return $relations;
    }

    /**
     * @param  string $field
     * @return array
     */
    public function getRelationData($field, $forOwner, $remoteClassId) {

        $id = $this->model->getO_id();
        if ($remoteClassId) {
            $classId = $remoteClassId;
        } else {
            $classId = $this->model->getO_classId();
        }


        $params = array($field, $id, $field, $id, $field, $id);

        $dest = "dest_id";
        $src = "src_id";
        if (!$forOwner) {
            $dest = "src_id";
            $src = "dest_id";
        }

        $relations = $this->db->fetchAll("SELECT r." . $dest . " as dest_id, r." . $dest . " as id, r.type, o.o_className as subtype, concat(o.o_path ,o.o_key) as path , r.index
            FROM objects o, object_relations_" . $classId . " r
            WHERE r.fieldname= ?
            AND r.ownertype = 'object'
            AND r." . $src . " = ?
            AND o.o_id = r." . $dest . "
            AND r.type='object'

            UNION SELECT r." . $dest . " as dest_id, r." . $dest . " as id, r.type,  a.type as subtype,  concat(a.path,a.filename) as path, r.index
            FROM assets a, object_relations_" . $classId . " r
            WHERE r.fieldname= ?
            AND r.ownertype = 'object'
            AND r." . $src . " = ?
            AND a.id = r." . $dest . "
            AND r.type='asset'

            UNION SELECT r." . $dest . " as dest_id, r." . $dest . " as id, r.type, d.type as subtype, concat(d.path,d.key) as path, r.index
            FROM documents d, object_relations_" . $classId . " r
            WHERE r.fieldname= ?
            AND r.ownertype = 'object'
            AND r." . $src . " = ?
            AND d.id = r." . $dest . "
            AND r.type='document'

            ORDER BY `index` ASC", $params);

        if (is_array($relations) and count($relations) > 0) {
            return $relations;
        } else return array();
    }


    /**
     * Get the data-elements for the object from database for the given path
     *
     * @return void
     */
    public function getData() {

        $data = $this->db->fetchRow('SELECT * FROM object_store_' . $this->model->getO_classId() . ' WHERE oo_id = ?', $this->model->getO_id());

        $allRelations = $this->db->fetchAll("SELECT * FROM object_relations_" . $this->model->getO_classId() . " WHERE src_id = ? AND ownertype = 'object' ORDER BY `index` ASC", $this->model->getO_id());

        foreach ($this->model->geto_class()->getFieldDefinitions() as $key => $value) {

            if ($value->isRelationType()) {

                if (!method_exists($value, "getLazyLoading") or !$value->getLazyLoading()) {

                    $relations = array();
                    foreach ($allRelations as $relation) {
                        if ($relation["fieldname"] == $key) {
                            $relations[] = $relation;
                        }
                    }
                } else {
                    $relations = null;
                }


                if (!empty($relations)) {
                    $this->model->setValue(
                        $key,
                        $value->getDataFromResource($relations));
                }


            } else if (method_exists($value, "load")) {
                // datafield has it's own loader
                $this->model->setValue(
                    $key,
                    $value->load($this->model));
            } else {
                // if a datafield requires more than one field
                if (is_array($value->getColumnType())) {
                    $multidata = array();
                    foreach ($value->getColumnType() as $fkey => $fvalue) {
                        $multidata[$key . "__" . $fkey] = $data[$key . "__" . $fkey];
                    }
                    $this->model->setValue(
                        $key,
                        $this->model->geto_class()->getFieldDefinition($key)->getDataFromResource($multidata));

                } else {
                    $this->model->setValue(
                        $key,
                        $value->getDataFromResource($data[$key]));
                }
            }
        }
    }

    /**
     * Save object to database
     *
     * @return void
     */
    public function save() {
        if ($this->model->getO_id()) {
            return $this->update();
        }
        return $this->create();
    }

    /**
     * Create a new record for the object in database
     *
     * @return boolean
     */
    public function create() {

        parent::create();

        $this->createDataRows();

        $this->save();
    }


    /**
     * create data rows for query table and for the store table
     *
     * @return void
     */
    protected function createDataRows() {
        try {
            $this->db->insert("object_store_" . $this->model->getO_classId(), array("oo_id" => $this->model->getO_id()));
        }
        catch (Exception $e) {
        }

        try {
            $this->db->insert("object_query_" . $this->model->getO_classId(), array("oo_id" => $this->model->getO_id()));
        }
        catch (Exception $e) {
        }
    }

    /**
     * Save changes to database, it's an good idea to use save() instead
     *
     * @return void
     */
    public function update() {
        parent::update();

        $this->createDataRows();

        $fd = $this->model->geto_class()->getFieldDefinitions();
        $untouchable = array();
        foreach ($fd as $key => $value) {
            if ($value->isRelationType()) {
                if ($value->getLazyLoading()) {
                    if (!in_array($key, $this->model->getLazyLoadedFields())) {
                        //this is a relation subject to lazy loading - it has not been loaded
                        $untouchable[] = $key;
                    }
                }
            }
        }


        // update data for store table
        if (count($untouchable) > 0) {
            $untouchables = "'" . implode("','", $untouchable) . "'";
            $this->db->delete("object_relations_" . $this->model->getO_classId(), "src_id = '" . $this->model->getO_id() . "' AND fieldname not in (" . $untouchables . ") AND ownertype = 'object'");
        } else {
            $this->db->delete("object_relations_" . $this->model->getO_classId(), "src_id = '" . $this->model->getO_id() . "' AND ownertype = 'object'");
        }

        $data = array();
        $data["oo_id"] = $this->model->getO_id();
        foreach ($fd as $key => $value) {
            if ($value->isRelationType()) {

                $getter = "get" . ucfirst($key);
                if (method_exists($this->model, $getter)) {
                    $relations = $value->getDataForResource($this->model->$getter());
                }

                if (is_array($relations) && !empty($relations)) {
                    foreach ($relations as $relation) {
                        $relation["src_id"] = $this->model->getId();
                        $relation["ownertype"] = "object";

                        /*relation needs to be an array with src_id, dest_id, type, fieldname*/
                        try {
                            $this->db->insert("object_relations_" . $this->model->getO_classId(), $relation);
                        } catch (Exception $e) {
                            Logger::warning("It seems that the relation " . $relation["src_id"] . " => " . $relation["dest_id"] . " already exist");
                        }
                    }
                }

                if (in_array($key, $untouchable) and $relations===null) {
                    logger::debug(get_class($this) . ": Excluding untouchable relation value for object [ " . $this->model->getId() . " ] key [ $key ] because it has not been loaded");
                }

            } else if ($value->getColumnType()) {
                if (is_array($value->getColumnType())) {
                    $insertDataArray = $value->getDataForResource($this->model->$key);
                    $data = array_merge($data, $insertDataArray);
                } else {
                    $insertData = $value->getDataForResource($this->model->$key);
                    $data[$key] = $insertData;
                }
            } else if (method_exists($value, "save")) {
                // for fieldtypes which have their own save algorithm eg. fieldcollections
                $value->save($this->model);
            }
        }

        // first try to insert a new record, this is because of the recyclebin restore
        try {
            $this->db->insert("object_store_" . $this->model->getO_classId(), $data);
        }
        catch (Exception $e) {
            $this->db->update("object_store_" . $this->model->getO_classId(), $data, "oo_id = " . $this->model->getO_id());
        }


        // get data for query table
        // this is special because we have to call each getter to get the inherited values from a possible parent object

        // HACK: set the pimcore admin mode to false to get the inherited values from parent if this source one is empty
        $inheritedValues = Object_Abstract::doGetInheritedValues();
        Object_Abstract::setGetInheritedValues(true);
        
        $object = get_object_vars($this->model);

        $data = array();
        $this->inheritanceHelper->resetFieldsToCheck();
        $oldData = $this->db->fetchRow("SELECT * FROM object_query_" . $this->model->getO_classId() . " WHERE oo_id = ?", $this->model->getId());

        foreach ($object as $key => $value) {
            $fd = $this->model->geto_class()->getFieldDefinition($key);

            if ($fd) {
                if ($fd->getQueryColumnType()) {
                    //exclude untouchables if value is not an array - this means data has not been loaded
                    if (!(in_array($key, $untouchable) and !is_array($this->model->$key))) {
                        $method = "get" . $key;
                        $insertData = $fd->getDataForQueryResource($this->model->$method());
                        if (is_array($insertData)) {
                            $data = array_merge($data, $insertData);
                        }
                        else {
                            $data[$key] = $insertData;
                        }

                        
                        //get changed fields for inheritance
                        if($fd->isRelationType()) {
                            if (is_array($insertData)) {
                                $doInsert = false;
                                foreach($insertData as $insertDataKey => $insertDataValue) {
                                    if($oldData[$insertDataKey] != $insertDataValue) {
                                        $doInsert = true;
                                    }
                                }

                                if($doInsert) {
                                    $this->inheritanceHelper->addRelationToCheck($key, array_keys($insertData));
                                }
                            } else {
                                if($oldData[$key] != $insertData) {
                                    $this->inheritanceHelper->addRelationToCheck($key);
                                }
                            }

                        } else {
                            if (is_array($insertData)) {
                                foreach($insertData as $insertDataKey => $insertDataValue) {
                                    if($oldData[$insertDataKey] != $insertDataValue) {
                                        $this->inheritanceHelper->addFieldToCheck($insertDataKey);
                                    }
                                }
                            } else {
                                if($oldData[$key] != $insertData) {
                                    $this->inheritanceHelper->addFieldToCheck($key);
                                }
                            }
                        }

                    } else {
                        logger::debug(get_class($this) . ": Excluding untouchable query value for object [ " . $this->model->getId() . " ]  key [ $key ] because it has not been loaded");
                    }
                }
            } 
        }
        $data["oo_id"] = $this->model->getO_id();

        // first try to insert a new record, this is because of the recyclebin restore
        try {
            $this->db->insert("object_query_" . $this->model->getO_classId(), $data);
        }
        catch (Exception $e) {
            $this->db->update("object_query_" . $this->model->getO_classId(), $data, "oo_id = " . $this->model->getO_id());
        }

        // HACK: see a few lines above!
        Object_Abstract::setGetInheritedValues($inheritedValues);
    }

    
    public function saveChilds() {
        $this->inheritanceHelper->doUpdate($this->model->getId());
        $this->inheritanceHelper->resetFieldsToCheck();
    }

    /**
     * Save object to database
     *
     * @return void
     */
    public function delete() {
        $this->db->delete("object_query_" . $this->model->getO_classId(), "oo_id = '" . $this->model->getO_id() . "'");
        $this->db->delete("object_store_" . $this->model->getO_classId(), "oo_id = '" . $this->model->getO_id() . "'");
        $this->db->delete("object_relations_" . $this->model->getO_classId(), "src_id = '" . $this->model->getO_id() . "'");
        $this->db->delete("object_relations_" . $this->model->getO_classId(), "dest_id = '" . $this->model->getO_id() . "'");

        // delete fields wich have their own delete algorithm
        foreach ($this->model->geto_class()->getFieldDefinitions() as $fd) {
            if (method_exists($fd, "delete")) {
                $fd->delete($this->model);
            }
        }

        parent::delete();
    }

    /**
     * get versions from database, and assign it to object
     *
     * @return array
     */
    public function getVersions() {
        $versionIds = $this->db->fetchAll("SELECT id FROM versions WHERE cid = '" . $this->model->getO_Id() . "' AND ctype='object' ORDER BY `id` DESC");

        $versions = array();
        foreach ($versionIds as $versionId) {
            $versions[] = Version::getById($versionId["id"]);
        }

        $this->model->setO_Versions($versions);

        return $versions;
    }

    /**
     * Get latest available version
     *
     * @return array
     */
    public function getLatestVersion() {
        $versionData = $this->db->fetchRow("SELECT id,date FROM versions WHERE cid = '" . $this->model->getO_Id() . "' AND ctype='object' ORDER BY `id` DESC LIMIT 1");

        if ($versionData["id"]  && $versionData["date"] != $this->model->getO_modificationDate()) {
            $version = Version::getById($versionData["id"]);
            return $version;
        }
        return;
    }

    /**
     * @return void
     */
    public function deleteAllTasks() {
        $this->db->delete("schedule_tasks", "cid='" . $this->model->getO_Id() . "' AND ctype='object'");
    }
}
