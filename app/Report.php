<?php

namespace App;

use App\Importer\UserImporter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $casts = [
        'request_data' => 'array',
        'body' => 'array'
    ];//TODO body column is not yet a json field, this cast won't work for now (i believe)
    protected $guarded = [];

    const PENDING_STATUS = 0;
    const RUNNING_STATUS = 1;
    const PROCESSED_STATUS = 2;
    const FAILED_STATUS = 3;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabel()
    {
        switch($this->status) {
            case self::PENDING_STATUS:
                return 'Pending';
            case self::RUNNING_STATUS:
                return 'Running';
            case self::PROCESSED_STATUS:
                return 'Processed';
            case self::FAILED_STATUS:
                return 'Failed';

        }
    }

    public function getRequestDataFormatted()
    {
        $requestDataFormatted = '';


        $requestData = $this->request_data;

        if (array_key_exists('sprints', $requestData) !== false) {
            $sprintTotal = (count($requestData['sprints'])  > 1)? 'milestones' : 'milestone';
            $requestDataFormatted = count($requestData['sprints']) .' '.$sprintTotal;
        } else if (array_key_exists('wikiname', $requestData) !== false) {
            $requestDataFormatted = $requestData['wikiname'];
        }


        if (array_key_exists('from_date', $requestData) && array_key_exists('to_date', $requestData)) {
            return $requestDataFormatted. ' from '.$requestData['from_date']. ' to '.$requestData['to_date'];
        }

        return $requestDataFormatted;

    }

    /**
     *
     * @param $userAssemblaId
     *
     * @return String
     */
    public function getUserName($userAssemblaId) {
        $userImporter = new UserImporter($this->user);
        $userName = $userImporter->getUserName($userAssemblaId);

        return $userName;
    }

    public function start()
    {
        $this->running();
    }

    protected function running()
    {
        $this->status = Report::RUNNING_STATUS;
        $this->save();
    }

    protected function processed($results)
    {
        $this->body = json_encode($results);
        $this->status = Report::PROCESSED_STATUS;
        $this->finished_at = Carbon::now();
        $this->save();
    }

    public function failed()
    {
        $this->status = Report::FAILED_STATUS;
        $this->save();
    }


}
