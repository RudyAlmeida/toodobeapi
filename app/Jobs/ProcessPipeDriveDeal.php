<?php

namespace App\Jobs;

use App\Http\Controllers\Services\PipeDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPipeDriveDeal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $pipeDriveData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pipeDriveData)
    {
        $this->pipeDriveData = $pipeDriveData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pipedriveService = new PipeDriveService();
        $pipedriveService->registrationFormFlow($this->pipeDriveData);
    }
}
