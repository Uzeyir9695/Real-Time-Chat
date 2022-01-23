<?php

namespace App\Jobs;

use App\Notifications\ChatNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user;
    public function __construct($user)
        {
            $this->user = $user;
        }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->user->notify((new ChatNotification($this->user)));
    }
}
