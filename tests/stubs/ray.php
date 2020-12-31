<?php

return [
    /*
     *  By default, this package will only try to transmit info to Ray
     *  when the environment is not production.
     */
    'enable' =>

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
