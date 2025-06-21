<?php

return [
    'driver' => 'file',
    'files' => STORAGE_PATH.'/framework/sessions',
    'cookie' => 'main_session',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'http_only' => true,
    'same_site' => null,
];
