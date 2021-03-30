<?php

namespace App\Http\Controllers;

use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Project;
use App\TicketTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TasksTimerController extends Controller
{
    public function index()
    {
        return view('taskstimer.index', [
            'tasks' => Auth::user()->tasks(),
            'projects' => Auth::user()->projects,
        ]);
    }

    /**
     * Persist the new resource
     */
    public function store()
    {
        $this->validateRequest();
        /** @var TicketDto $validTicket */
        $validTicket = $this->validateTicket();

        if ($validTicket !== false) {
            TicketTime::create([
                'description' => $validTicket->getSummary(),
                'begin_at' => Carbon::now(),
                'ticket_number' => request('ticket_number'),
                'project_assembla_id' => request('project'),
                'user_assembla_id' => Auth::user()->user_assembla_id
            ]);

            return redirect(route('taskstimer.index'));
        } else {
            return redirect(route('taskstimer.index'))->withErrors([
                'ticket_number' => 'Ticket number does not exists or is a User Story',
            ])->withInput();
        }

    }

    /**
     * @return array
     */
    protected function validateRequest()
    {
        return request()->validate([
            'project'   => 'required',
            'ticket_number' => ['required','integer', 'min:1'],
        ]);
    }

    protected function validateTicket()
    {
        $project = Project::getProjectByAssemblaId(request('project'));
        $assemblaGateway = new AssemblaGateway(Auth::user());
        return $assemblaGateway->validateTicketExistsBySpaceAndNumber($project->wikiname, request('ticket_number'), ['is_story' => false]);
    }
}
