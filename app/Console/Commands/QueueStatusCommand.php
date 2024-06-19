<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\QueueManager;

class QueueStatusCommand extends Command
{
    protected $signature = 'queue:status';
    protected $description = 'Show real-time status of queues';

    protected $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        parent::__construct();

        $this->queueManager = $queueManager;
    }

    public function handle()
    {
        // Get the names of the configured queues
        $queues = array_keys(config('queue.connections'));

        $this->info('Queues: ' . implode(', ', $queues));

        foreach ($queues as $queue) {
            // Get the number of waiting jobs in the queue
            $waitingJobs = $this->queueManager->size($queue);

            // Get the number of reserved jobs in the queue
            $reservedJobs = $this->getReservedJobsCount($queue);

            $this->info("Queue: $queue, Waiting Jobs: $waitingJobs, Reserved Jobs: $reservedJobs");
        }
    }

    protected function getReservedJobsCount($queue)
    {
        // For the SyncQueue, we can't directly get the reserved jobs count
        // Instead, we'll consider it as zero, as SyncQueue processes jobs immediately
        return 0;
    }
}