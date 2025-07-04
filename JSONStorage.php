<?php

class JSONStorage
{
    protected string $tableName;
    protected string $dbPath;

    public function __construct(string $dbPath = __DIR__ . '/json_db')
    {
        $this->dbPath = rtrim($dbPath, '/');

        if (!is_dir($this->dbPath)) {
            mkdir($this->dbPath, 0777, true);
        }
    }

    public function
    setTable(string $name): self
    {
        $this->tableName = $name;
        return $this;
    }

    protected function getFile(): string
    {
        return "{$this->dbPath}/{$this->tableName}.json";
    }

    protected function getMetaFile(): string
    {
        return "{$this->dbPath}/.meta.json";
    }

    protected function load(): array
    {
        $file = $this->getFile();

        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }

        return json_decode(file_get_contents($file), true);
    }

    protected function save(array $records): void
    {
        file_put_contents($this->getFile(), json_encode($records, JSON_PRETTY_PRINT));
    }

    protected function getNextId(): int
    {
        $metaFile = $this->getMetaFile();
        $meta = [];

        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);
        }

        if (!isset($meta[$this->tableName])) {
            $meta[$this->tableName] = 1;
        } else {
            $meta[$this->tableName]++;
        }

        file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
        return $meta[$this->tableName];
    }
}
