<?php

return [
    // 'credentials' => storage_path('app/firebase-credentials.json'),
    'credentials' =>json_decode(base64_decode(env('FIREBASE_CREDENTIALS'))),

    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],
    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],
];