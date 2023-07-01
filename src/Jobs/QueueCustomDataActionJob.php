<?php

namespace Kakaprodo\CustomData\Jobs;

use Illuminate\Bus\Queueable;
use Kakaprodo\CustomData\CustomData;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Kakaprodo\CustomData\Helpers\CustomActionBuilder;

class QueueCustomDataActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * the payload to pass to the Action class
     */
    public $data;

    /**
     * the action instance to execute
     * 
     * @var CustomActionBuilder
     */
    public $action;

    /**
     * the method to call on the action that is going to execute 
     * the action logic
     */
    public $actionHandlerMethod;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        CustomActionBuilder $action,
        CustomData $customData,
        $actionHandlerMethod = 'handle'
    ) {
        $this->data = $customData;
        $this->action = $action;
        $this->actionHandlerMethod = $actionHandlerMethod;

        if ($action->onQueue) {
            $this->queue = $action->onQueue;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $handler = $this->actionHandlerMethod;

        return $this->action->$handler($this->data);
    }
}
