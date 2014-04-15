<?php

namespace UniMapper\Mapper;

use UniMapper\Query,
    UniMapper\Exceptions\MapperException;

class MongoMapper extends \UniMapper\Mapper
{

    /** @var \MongoDB */
    private $database;

    private $defaultConfig = [
        "host" => "localhost",
        "port" => 27017,
        "username" => null,
        "password" => null,
        "database" => null,
        "options" => []
    ];

    public function __construct(array $config, $name)
    {
        parent::__construct($name);
        if ($config["database"] === null) {
            throw new MapperException("No database selected!");
        }
        $this->database = $this->createConnection($this->defaultConfig + $config)->selectDB($config["database"]);
    }

    private function createConnection($config)
    {
        $url = "mongodb://";
        if ($config["username"] !== null) {

            $url .= $config["username"];
            if ($config["password"] !== null) {
                $url .=":" . $config["password"];
            }
            $url .= "@";
        }
        $url .= $config["host"];
        $url .= ":" . $config["port"];

        return new \MongoClient($url, $config["options"]);
    }

    public function custom(Query\Custom $query)
    {
        throw new MapperException("Not implemented!");
    }

    public function delete(Query\Delete $query)
    {
        throw new MapperException("Not implemented!");
    }

    public function findOne(Query\FindOne $query)
    {
        throw new MapperException("Not implemented!");
    }

    public function findAll(Query\FindAll $query)
    {
        $collectionName = $this->getResource($query->entityReflection);

        $collection = $this->database->{$collectionName};
        if (!$collection) {
            throw new MapperException("Collection with name " . $collectionName . " not found!");
        }

        $selection = array_fill_keys($this->getSelection($query->entityReflection), true);

        // @todo conditions, offset http://us2.php.net/manual/en/mongocollection.find.php
        $result = $collection->find(array(), $selection)->limit($query->limit);
        if (!$result) {
            return false;
        }

        return $this->mapCollection($query->entityReflection->getClassName(), $result);
    }

    public function count(Query\Count $query)
    {
        throw new MapperException("Not implemented!");
    }

    public function insert(Query\Insert $query)
    {
        $values = $this->unmapEntity($query->entity);
        if (empty($values)) {
            throw new MapperException("Entity has no mapped values!");
        }

        $collectionName = $this->getResource($query->entityReflection);

        $collection = $this->database->{$collectionName};
        if (!$collection) {
            throw new MapperException("Collection with name " . $collectionName . " not found!");
        }

        $result = $collection->insert($values);
        if ($result["err"] !== null) {
            throw new MapperException($result["err"]);
        }

        if ($query->returnPrimaryValue) {
            return $values[$query->entityReflection->getPrimaryProperty()->getName()];
        }
    }

    public function update(Query\Update $query)
    {
        throw new MapperException("Not implemented!");
    }

}