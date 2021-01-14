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
        $requestData = unserialize($this->request_data);
        $wikiname = (array_key_exists('wikiname', $requestData))?$requestData['wikiname']:'';

        if (array_key_exists('from_date', $requestData) && array_key_exists('to_date', $requestData)) {
            return $wikiname. ' from '.$requestData['from_date']. ' to '.$requestData['to_date'];
        }

        return $wikiname;

    }
}
