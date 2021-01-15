<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

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

        $serializedRequest = $this->request_data;
        $requestData = unserialize($this->request_data);
        if (strpos($serializedRequest, 'sprints') !== false) {
            $sprintTotal = (count($requestData['sprints'])  > 1)? 'milestones' : 'milestone';
            $requestDataFormatted = count($requestData['sprints']) .' '.$sprintTotal;

        } else if (strpos($serializedRequest, 'wikiname') !== false) {
            $requestData = unserialize($this->request_data);
            $requestDataFormatted = $requestData['wikiname'];
        }


        if (array_key_exists('from_date', $requestData) && array_key_exists('to_date', $requestData)) {
            return $requestDataFormatted. ' from '.$requestData['from_date']. ' to '.$requestData['to_date'];
        }

        return $requestDataFormatted;

    }
}
