
<?php

namespace App\Events;

class StylistRequestSubmitted
{
    public $user_id;
    public $request_id;
    public $request_time;

    /**
     * Create a new event instance.
     *
     * @param  int  $user_id
     * @param  int  $request_id
     * @param  \DateTime  $request_time
     * @return void
     */
    public function __construct($user_id, $request_id, $request_time)
    {
        $this->user_id = $user_id;
        $this->request_id = $request_id;
        $this->request_time = $request_time;
    }
}
