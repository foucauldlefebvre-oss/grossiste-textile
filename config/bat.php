<?php

return [
    'admin_emails' => array_filter(explode(',', env('BAT_ADMIN_EMAILS', ''))),
];
