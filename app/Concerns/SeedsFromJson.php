<?php

namespace Database\Seeders\Concerns;

trait SeedsFromJson
{
    
    protected function readJson(string $file): array
    {
        return json_decode(
            file_get_contents(database_path("seed-data/{$file}.json")),
            true
        );
    }

    
    protected function seedFromJson(string $model, string $file, ?callable $transform = null): void
    {
        foreach ($this->readJson($file) as $row) {
            if ($transform) {
                $row = $transform($row);
            }
            $model::forceCreate($row);
        }
    }
}
