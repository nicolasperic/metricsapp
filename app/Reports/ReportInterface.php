<?php

namespace App\Reports;

/**
 * This interface is used for shared actions on all Reports
 *
 * Interface ReportInterface
 *
 * @package App\Reports
 */
interface ReportInterface
{
    public function getNotificationMessage();

    public function getRequestDataFormatted();

    public function getView();

}