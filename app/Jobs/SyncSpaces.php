<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Importer\ProjectImporter;
use App\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSpaces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $projectImporter = new ProjectImporter($this->user);
            $projectImporter->importAllAssemblaSpacesAsProjects();
            $this->user->notify($this->_createNotification('Spaces were synced correctly', route('projects.index')));
        } catch (ClientException $e) {
            if ($e->getCode() == 401) {
                $errorMessage = 'Not authorized! Update your Assembla credentials';
                $url = route('settings.index');
            } else {
                $errorMessage = 'Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.';
                $url = route('home');//contact-support : )
            }

            $this->user->notify($this->_createNotification($errorMessage, $url,  'bg-warning'));
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } catch (\Exception $e) {
            $errorMessage = 'Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.';
            $this->user->notify($this->_createNotification($errorMessage, route('home'), 'bg-warning'));//maybe instead of home /contact-support form : )
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    private function _createNotification($message, $url, $bgClass = 'bg-success')
    {
        return Helper::getAssemblaSyncNotification(
            null,
            $url,
            $message,
            $bgClass
        );
    }
}
