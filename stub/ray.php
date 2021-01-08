<?php

return [
    /*
    * This settings controls whether data should be sent to Ray.
    *
    * By default, `ray()` will only transmit data in non-production environments.
    */
    'enable' => true,

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
    * The host used to communicate with the Ray app.
    * For usage in Docker on Mac or Windows, you can replace host with 'host.docker.internal'
    * For usage in Homestead on Mac or Windows, you can replace host with '10.0.2.2'
    */
    'host' => 'localhost',

    /*
    * The port number used to communicate with the Ray app.
    */
    'port' => 23517,
];
