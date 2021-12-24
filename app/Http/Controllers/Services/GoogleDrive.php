<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Google_Service_Drive_Permission;
use Illuminate\Support\Facades\Storage;

class GoogleDrive extends Controller
{
    private $disk;

    private $marketingDirectory = '1jwVNJ1KvpzcqL6VGfuP9dorECULIMrce';

    private $userPhotoDiretory = '1Zx5jF6fxn4A6ETfJqdCWVDM2_zjydrSn';

    private $documentsDirectory = '1p6HbbLElYA4fGgwuFZnPzeV3AfquFObE';


    public function __construct()
    {
        $this->disk = Storage::cloud();
    }

    public function marketingList()
    {

        $array = [];

        $files =  collect(Storage::cloud()->listContents($this->marketingDirectory, true))
            ->where('type', '=', 'file')
            ->all();

        $service = Storage::cloud()->getAdapter()->getService();
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');
        $permission->setAllowFileDiscovery(false);


        foreach ($files as $file){

            $service->permissions->create($file['basename'], $permission);

            $array[] = [
                'name' => $file['name'],
                'type'=> $file['type'],
                'filename'=> $file['filename'],
                'path' =>  Storage::cloud()->url($file['path']),
                'mimetype' => $file['mimetype'],
                'extension' => $file['extension']
            ];

        }


        return $array;

    }

    public function uploadDocumentFile($registry_code, $file, $name)
    {
        $registry_code = preg_replace('/\D/', '', $registry_code);
        $directory = $this->userDocumentsDirectory($registry_code);

        $this->keepOnlyOneFile($directory, $name);

        Storage::cloud()->put($directory . '/' . $name, $file);

        $contents = collect(Storage::cloud()->listContents($directory, false));
        $cloudFile = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($name, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($name, PATHINFO_EXTENSION))
            ->first();

        $service = Storage::cloud()->getAdapter()->getService();
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');
        $permission->setAllowFileDiscovery(false);
        $service->permissions->create($cloudFile['basename'], $permission);

        return Storage::cloud()->url($cloudFile['path']);
    }

    public function userDocumentsDirectory($registry_code)
    {
        $registry_code = preg_replace('/\D/', '', $registry_code);

        if (!$directory = $this->verifyUserDocumentsDirectory($registry_code)) {
            Storage::cloud()->makeDirectory($this->documentsDirectory . '/' . $registry_code);
            $contents = collect(Storage::cloud()->listContents($this->documentsDirectory, false));
            $directory = $contents->where('type', '=', 'dir')
                ->where('filename', '=', $registry_code)
                ->first();
        }
        return $directory['path'];
    }

    private function verifyUserDocumentsDirectory($registry_code)
    {
        $registry_code = preg_replace('/\D/', '', $registry_code);

        $contents = collect(Storage::cloud()->listContents($this->documentsDirectory, false));

        return $contents->where('type', '=', 'dir')
            ->where('filename', '=', $registry_code)
            ->first();
    }

    public function uploadUserPhoto($file, $name)
    {

        $this->keepOnlyOneFile($this->userPhotoDiretory, $name);

        Storage::cloud()->put($this->userPhotoDiretory . '/' . $name, $file);
        $contents = collect(Storage::cloud()->listContents($this->userPhotoDiretory, false));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($name, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($name, PATHINFO_EXTENSION))
            ->first();

        $service = Storage::cloud()->getAdapter()->getService();
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');
        $permission->setAllowFileDiscovery(false);
        $service->permissions->create($file['basename'], $permission);

        return Storage::cloud()->url($file['path']);

    }

    private function keepOnlyOneFile($directory, $filename)
    {

        $contents = collect(Storage::cloud()->listContents($directory, false));

        $files = $contents
            ->where('type', '=', 'file')
            ->where('filename', 'like', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->all();

        foreach ($files as $file) {
            Storage::cloud()->delete($file['path']);
        }

    }

}
