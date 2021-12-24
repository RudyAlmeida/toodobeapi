<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\GoogleDrive;
use App\MarketingFiles;
use Illuminate\Http\Request;

class Marketing extends Controller
{
    /**
     *
     */
    public function syncFiles()
    {
        $googleDrive = new GoogleDrive();

        foreach ($googleDrive->marketingList() as $array) {
            $this->saveToDatabase($array);
        }

    }

    /**
     * @param $array
     */
    private function saveToDatabase($array)
    {
        MarketingFiles::updateOrCreate(
            ['filename' => $array['filename'], 'path' => $array['path']],
            $array
        );
    }

    public function index(Request $request)
    {
        $this->syncFiles();

        $per_page = isset($request->per_page) ? $request->per_page : 10;
        $search = isset($request->search) ? $request->search : '';

        return MarketingFiles::query()
            ->where('name', 'like', $search . '%')
            ->paginate($per_page)
            ->appends('per_page', $per_page)
            ->appends('search', $search);
    }

}
