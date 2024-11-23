<?php

return [
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    'is_3d_secure' => env('MIDTRANS_IS_3D_SECURE', false),
    'is_token' => env('MIDTRANS_IS_TOKEN', false),
    'is_uat' => env('MIDTRANS_IS_UAT', false),
    'is_dev' => env('MIDTRANS_IS_DEV', false),
    'is_live' => env('MIDTRANS_IS_LIVE', false),
    'is_sandbox' => env('MIDTRANS_IS_SANDBOX', false),
    'is_staging' => env('MIDTRANS_IS_STAGING', false),

    'is_3ds' => env('MIDTRANS_IS_3DS', true),
    'is_3ds_v2' => env('MIDTRANS_IS_3DS_V2', false),
    'is_3ds_v3' => env('MIDTRANS_IS_3DS_V3', false),
    'is_3ds_v4' => env('MIDTRANS_IS_3DS_V4', false),
];
