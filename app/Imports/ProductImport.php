<?php

namespace App\Imports;

use App\Models\Products;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\CustomerPhone;

class ProductImport implements ToCollection
{
    public $collection;
    public $rowErrors = [];
    protected $NameIndex;

    public function __construct(array $mapping)
    {
        $this->NameIndex = isset($mapping[0]) ? (int) $mapping[0] : null;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('The uploaded file is empty.');
        }

        $this->collection = $rows;

        foreach ($rows as $index => $row)
        {
            $row = $row->toArray();
            $message = '';

            // Skip header row
            if ($index === 0) {
                continue;
            }

           if ($this->NameIndex !== null && isset($row[$this->NameIndex]))
            {
                $name = trim($row[$this->NameIndex]);

                // Empty check
                if ($name === '') {
                    $message = "Name is empty.";
                }
                // Cannot be only numbers
                elseif (preg_match('/^[0-9]+$/', $name)) {
                    $message = "Name cannot be only numbers.";
                }

                elseif (str_contains($name, '@')) {
                    $message = "Name cannot contain '@'.";
                }
                else
                {
                    $exists = Products::where('name', 'like', "%{$name}%")->first();

                    if ($exists) {
                        $message = "Name '{$name}' already exists.";
                    }
                }
            }
            else
            {
                $message = "Name is required.";
            }


            $this->rowErrors[$index] = $message;
        }
    }
}
