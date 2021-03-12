<?php

//TODO move all Assembla actions to controllers under the Controllers/Assembla namespace
namespace App\Http\Controllers\Assembla;

use App\Helper\SessionMessage;
use App\Http\Controllers\Controller;
use App\Jobs\SyncSpaces;
use Illuminate\Support\Facades\Auth;


class SyncSpacesController extends Controller
{
    /**
     * This function is used for importing user projects (assembla spaces)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync()
    {
        SyncSpaces::dispatch(Auth::user());
        SessionMessage::infoMessage("Spaces sync job was added to the queue");

        return redirect()->route('projects.index');
    }
}
