<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\CustomerPhone;

class LeadImport implements ToCollection
{
    public $collection;
    public $rowErrors = [];
    protected $phoneIndex;

    public function __construct(array $mapping)
    {
        $this->phoneIndex = isset($mapping[2]) ? (int) $mapping[2] : null;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('The uploaded file is empty.');
        }

        $this->collection = $rows;

        foreach ($rows as $index => $row) {
            $row = $row->toArray();
            $message = '';

            // Skip header row
            if ($index === 0) {
                continue;
            }

            if ($this->phoneIndex !== null && isset($row[$this->phoneIndex])) {
                $phone = preg_replace('/\D/', '', $row[$this->phoneIndex]);

                if (strlen($phone) < 10) {
                    $message = "Phone number is less than 10 digits.";
                } elseif (strlen($phone) > 10) {
                    $message = "Phone number is greater than 10 digits.";
                } else {
                    // Check in DB
                    // $phoneExists = \DB::table('entities')
                    //     ->where('type', 'customer')
                    //     ->where('contact', $phone)
                    //     ->exists();

                    $phoneExists = CustomerPhone::where('phone',$phone)->where('is_primary',1)->exists();
                    if ($phoneExists) {
                        $message .= " This phone number already exists.";
                    }
                }
            } else {
                $message = "Phone number is required.";
            }

            $this->rowErrors[$index] = $message;
        }
    }
}
