<?php

namespace App\Http\Controllers;

use App\Dto\AssemblaUserDto;
use App\Helper\SessionMessage;
use App\Integration\AssemblaGateway;

use App\Jobs\SyncSpaces;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{

    public function index()
    {
        return view('settings.index', ['user' => Auth::user()]);
    }

    /**
     * Generates report
     */
    public function store()
    {
        $this->validateRequest();

        $user = Auth::user();
        $syncSpaceMessage = '';
        if ($this->assemblaCredentialsUpdated($user)) {
            $user->assembla_key = request('assembla_key');
            $user->assembla_secret = Crypt::encrypt(request('assembla_secret'));
            if (!$this->_setUserImageAndId($user)) {
                return redirect(route('settings.index'))->withErrors([
                    'assembla_secret' => 'Assembla Secret is not valid',
                    'assembla_key'    => 'Assembla Key is not valid',
                ])->withInput();
            } else {
                SyncSpaces::dispatch($user);
                $syncSpaceMessage = '. Spaces sync job was added to the queue.';
            }
        }

            $user->name = request('name');
            $user->email = request('email');
            $user->save();
            SessionMessage::infoMessage('Settings saved'.$syncSpaceMessage);

            return redirect(route('settings.index'));
    }

    private function assemblaCredentialsUpdated($user)
    {
        return request('assembla_key') != $user->assembla_key ||
        Crypt::decrypt($user->assembla_secret) != request('assembla_secret');
    }

    //TODO this function doesn't belong here
    private function _setUserImageAndId($user)
    {
        $success = false;
        $gateway = new AssemblaGateway(Auth::user());
        try {
            /** @var AssemblaUserDto $assemblaUserDto */
            $assemblaUserDto = $gateway->getAuthenticatedUser();
            $imagePath = $gateway->getUserImage($assemblaUserDto->getUserAssemblaId());
            $user->assembla_user_image = $imagePath;
            $user->assembla_username = $assemblaUserDto->getName();
            $user->user_assembla_id = $assemblaUserDto->getUserAssemblaId();
            $user->save();
            $success = true;
        }  catch (ClientException $e) {

            if ($e->getCode() == 401) {
                SessionMessage::errorMessage('Not authorized! Your Assembla credentials are not valid');
            } else {
                SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            }

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return $success;

    }

    /**
     * @return array
     */
    protected function validateRequest()
    {
        return request()->validate([
            'name'   => 'required',
            'email'   => 'required',
            'assembla_secret' => 'required',
            'assembla_key' => 'required',
        ]);
    }
}
