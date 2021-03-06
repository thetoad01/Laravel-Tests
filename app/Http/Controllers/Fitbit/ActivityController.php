<?php

namespace App\Http\Controllers\Fitbit;

use App\Http\Controllers\Controller;
use App\Rules\Csv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use League\Csv\Reader;
use App\Models\Fitbit\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        return view('fitbit.activity.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('fitbit.activity.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', new Csv],
        ]);

        $path = Storage::putFileAs('fitbit', $request->file('file'), 'fitbit_export_activity.csv');

        $file = Storage::disk('local')->get($path);

        $csv = Reader::createFromString($file);
        $csv->setHeaderOffset(1);
        $header_offset = $csv->getHeaderOffset();
        $header = $csv->getHeader();
        $records = $csv->getRecords();

        $output = [];
        foreach ($records as $offset => $record) {
            if ( isset($record['Calories Burned']) && strtotime($record['Date']) && ! strtotime($record['Calories Burned']) ) {

                $result = Activity::updateOrCreate(
                    [
                        'date' => $record['Date'],
                    ],
                    [
                        'calories_burned' => (int) str_replace(',', '', $record['Calories Burned']),
                        'steps' => (int) str_replace(',', '', $record['Steps']),
                        'distance' => (float) str_replace(',', '', $record['Distance']),
                        'floors' => (int) str_replace(',', '', $record['Floors']),
                        'minutes_sedentary' => (int) str_replace(',', '', $record['Minutes Sedentary']),
                        'minutes_lightly_active' => (int) str_replace(',', '', $record['Minutes Lightly Active']),
                        'minutes_fairly_active' => (int) str_replace(',', '', $record['Minutes Fairly Active']),
                        'minutes_very_active' => (int) str_replace(',', '', $record['Minutes Very Active']),
                        'activity_calories' => (int) str_replace(',', '', $record['Activity Calories']),
                    ]
                );

                $output[] = $result;
            }
        }

        return view('fitbit.activity.upload-success', [
            'data' => collect($output),
        ]);
    }
}
