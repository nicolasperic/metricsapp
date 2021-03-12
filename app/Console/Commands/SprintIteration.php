<?php

namespace App\Console\Commands;

use App\SprintIteration as SprintIterationModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SprintIteration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sprintiteration:iterate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        $iterations = SprintIterationModel::where('next_iteration_start_date', $today)->get();

        $infoMessage = 'No iterations for today';
        if (count($iterations)) {
            /** @var \App\SprintIteration $iteration */
            foreach ($iterations as $iteration) {
                $iteration->iterate();
            }

            $infoMessage = 'Iterated '.count($iterations).' '.Str::plural('sprint', count($iterations));
        };



        $this->info($infoMessage);
    }
}
