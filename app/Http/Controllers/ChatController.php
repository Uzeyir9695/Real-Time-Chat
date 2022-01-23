<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\Events\SendMailEvent;
use App\Jobs\ChatJob;
use App\Models\Chat;
use App\Models\User;
use App\Notifications\ChatNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Laravie\SerializesQuery\Eloquent; //serialization package


class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chat.chat');
    }

    public function users()
    {
        return User::where('id', '!=', auth()->user()->id)->get();
    }

    public function fetchAllMessages(User $user)
    {
        $model = Chat::with('user');
        $serializedModel = Eloquent::serialize($model);
        Redis::set('ChatModel', serialize($serializedModel));

        if(Redis::exists('ChatModel')) {
            $model = Redis::get('ChatModel');
            $unserializedModel = unserialize($model);

            return Eloquent::unserialize($unserializedModel)->where(['user_id' => auth()->id(), 'receiver_id' => $user->id])
                ->orWhere(function($query) use($user){
                    $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
                })->get();
        }

//        $privateMessages = Chat::with('user')
//            ->where(['user_id' => auth()->id(), 'receiver_id' => $user->id])
//            ->orWhere(function($query) use($user){
//                $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
//            })->get();
//
//        return $privateMessages;

    }

    public function sendMessage(Request $request, User $user)
    {
            $message = auth()->user()->messages()->create([
                'user_id' => request()->user()->id,
                'message' => $request->message,
                'receiver_id' => $user->id
            ]);

        broadcast(new ChatEvent($message->load('user')))->toOthers();

        $delation = Carbon::now()->addSeconds(15);
        dispatch(new ChatJob($user))->delay($delation);
        return response(['status'=>'Message private sent successfully','message'=>$message]);

    }

}
