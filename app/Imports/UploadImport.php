<?php

namespace App\Imports;

use App\Models\ChangeValidation;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class UploadImport implements ToCollection,
    WithHeadingRow, 
    SkipsOnError
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    // public function model(array $row)
    // {

    //     dd($row, $row['Farmer UniqueID']);
    //     return new User([
    //         //
    //     ]);
    // }

    use Importable, SkipsErrors;
    private $rows = 0;
    public function collection(Collection $rows)
    {
        // dd(ChangeValidation::all());
        foreach ($rows as $row) 
        {
            // dd($row);
            ++$this->rows;
            ChangeValidation::create([
                'farmer_uniqueId' => $row['farmer_uniqueid'],
                'farmer_plot_uniqueid' => $row['plot_unique_id'],
                'plot_no' =>  $row['plot_no'],
                'created_at'=> Carbon::now(),
            ]);
        }
    }

}
