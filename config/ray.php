<?php

return [
    /*
     *  By default, this package will only try to transmit info to Ray
     *  when APP_DEBUG is set to `true`.
     */
    'enable_ray' => (bool) env('APP_DEBUG', false),

    /*
     * The port number to communicate with Ray.
     */
    'port' => 23517,

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
];
