<?php

namespace App;

use App\Helper\Helper;
use App\Importer\UserImporter;
use App\Notifications\ReportProcessed;
use App\Reports\HoursByUserReport;
use App\Reports\HoursByUSReport;
use App\Reports\SprintsReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use MorphTrait;//used to swap Report instance on creation based on type value

    const REPORT_TYPE = 'report';//no instance should be found on DB with this report type

    protected $morphKey = 'type';
    protected $morphMap = [
        HoursByUSReport::REPORT_TYPE   => HoursByUSReport::class,
        HoursByUserReport::REPORT_TYPE => HoursByUserReport::class,
        SprintsReport::REPORT_TYPE     => SprintsReport::class
    ];


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

    /**
     * Specifying type value when creating a new instance
     * All Reports must extend the const REPORT_TYPE
     */
    public static function boot()
    {
        parent::boot();

        // Save the type when creating this model
        static::creating(function ($report) {
            $report->forceFill([
                'type' => static::REPORT_TYPE,
            ]);
        });
    }

    /**
     * Returns the status label
     * 0 > Pending
     * 1 > Running
     * 2 > Processed
     * 3 > Failed
     * @return string
     */
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
        $this->user->notify(new ReportProcessed($this));
        $this->save();
    }

    public function failed()
    {
        $this->status = Report::FAILED_STATUS;
        $this->save();
    }

    public function isProcessed()
    {
        return $this->status === Report::PROCESSED_STATUS;
    }

    protected function getFromDateLabel()
    {
        return Helper::getDateWithoutHours($this->request_data['from_date']);
    }

    protected function getToDateLabel()
    {
        return Helper::getDateWithoutHours($this->request_data['to_date']);
    }


}
