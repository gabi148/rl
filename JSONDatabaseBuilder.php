<?php

require_once 'JSONStorage.php';

class JSONDatabaseBuilder extends JSONStorage
{
    protected array $filters = [];

    public function table(string $name): self
    {
        $this->setTable($name);
        $this->filters = [];
        return $this;
    }

    public function where(string $column, mixed $value): self
    {
        $this->filters[] = ['column' => $column, 'value' => $value];
        return $this;
    }

    protected function applyFilters(array $records): array
    {
        return array_filter($records, function ($record) {
            foreach ($this->filters as $filter) {
                if (!isset($record[$filter['column']]) || $record[$filter['column']] != $filter['value']) {
                    return false;
                }
            }
            return true;
        });
    }

    public function get(array $columns = ['*']): array
    {
        $filtered = $this->applyFilters($this->load());

        if ($columns === ['*']) {
            return array_values($filtered);
        }

        return array_map(function ($record) use ($columns) {
            return array_intersect_key($record, array_flip($columns));
        }, $filtered);
    }

    public function first(array $columns = ['*']): ?array
    {
        $results = $this->get($columns);
        return $results[0] ?? null;
    }

    public function insert(array $record): bool
    {
        $records = $this->load();

        if (!isset($record['id'])) {
            $record['id'] = $this->getNextId();
        }

        $records[] = $record;
        $this->save($records);
        return true;
    }

    public function update(array $newData): int
    {
        $records = $this->load();
        $count = 0;

        foreach ($records as &$record) {
            if ($this->matches($record)) {
                $record = array_merge($record, $newData);
                $count++;
            }
        }

        $this->save($records);
        return $count;
    }

    public function delete(): int
    {
        $records = $this->load();
        $remaining = [];
        $count = 0;

        foreach ($records as $record) {
            if ($this->matches($record)) {
                $count++;
                continue;
            }
            $remaining[] = $record;
        }

        $this->save($remaining);
        return $count;
    }

    protected function matches(array $record): bool
    {
        foreach ($this->filters as $filter) {
            if (!isset($record[$filter['column']]) || $record[$filter['column']] != $filter['value']) {
                return false;
            }
        }
        return true;
    }

    // === Morph Relations ===

    /**
     * Get morphMany relation records.
     *
     * @param string $relatedTable The related table name (e.g., 'comments')
     * @param string $morphTypeField The field name holding morph type (e.g., 'morph_type')
     * @param string $morphIdField The field name holding morph id (e.g., 'morph_id')
     * @param int $parentId The ID of the parent record
     * @return array
     */
    public function morphMany(string $relatedTable, string $morphTypeField, string $morphIdField, int $parentId): array
    {
        return $this->table($relatedTable)
            ->where($morphTypeField, $this->tableName)
            ->where($morphIdField, $parentId)
            ->get();
    }

    /**
     * Get the morphTo relation parent record.
     *
     * @param string $morphTypeField The field name holding morph type
     * @param string $morphIdField The field name holding morph id
     * @return array|null
     */
    public function morphTo(string $morphTypeField, string $morphIdField): ?array
    {
        $record = $this->first();

        if (!$record || !isset($record[$morphTypeField], $record[$morphIdField])) {
            return null;
        }

        $parentTable = $record[$morphTypeField];
        $parentId = $record[$morphIdField];

        return $this->table($parentTable)->where('id', $parentId)->first();
    }
}
