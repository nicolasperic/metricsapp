<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStat extends Model
{
    use HasFactory;

    const WEEK_RANGE_TYPE = 1;
    const MONTH_RANGE_TYPE = 2;
    const YEAR_RANGE_TYPE = 3;

    protected $guarded = [];
}
