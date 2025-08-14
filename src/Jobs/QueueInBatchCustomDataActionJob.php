<?php

namespace Kakaprodo\CustomData\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Kakaprodo\CustomData\CustomData;

class QueueInBatchCustomDataActionJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * the payload to pass to the Action class
     * @var array
     */
    public $payload;

    /**
     * the action instance to execute
     * 
     * @var string
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
     * @param string $action
     * @param array|CustomData $action
     * @return void
     */
    public function __construct(
        string $action,
        $payload
    ) {
        $this->payload = $payload;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $batch = $this->batch();

        if ($batch->cancelled()) {
            return;
        }

        $actionClass = $this->action;

        return $actionClass::process($this->payload, function (CustomData $data) use ($batch) {
            $data->batch = $batch;
        });
    }

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName()
    {
        return class_basename($this->action);
    }
}
