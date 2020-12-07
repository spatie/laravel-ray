<?php

return [
    /*
     * When enabled, all things logged to the application log
     * will be sent to Ray as well.
     */
    'send_log_calls_to_ray' => true,


    /*
     * When enabled, all things passed to `dump` or `dd`
     * will be sent to Ray as well.
     */
    'send_dumps_to_ray' => true,

    /*
     * The port number to communicate with Ray.
     */
    'port' => 23517,
];
