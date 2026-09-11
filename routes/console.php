<?php

use Illuminate\Support\Facades\Schedule;

/*
| Scheduled maintenance
|--------------------------------------------------------------------------
| Scheduled content needs no job of its own: the public queries compare
| `published_at` against the current time, so an article goes live on its own.
| What is scheduled here is housekeeping.
*/

// Failed jobs are worth keeping long enough to investigate a failed receipt,
// but not indefinitely.
Schedule::command('queue:prune-failed --hours=336')->daily();

Schedule::command('queue:prune-batches --hours=336')->daily();

// Expired sessions and password reset tokens.
Schedule::command('auth:clear-resets')->daily();

// Rebuild the cached config, routes and views after a scheduled deployment
// leaves them stale is a deploy concern, not a scheduled one - see docs/deployment.md.
