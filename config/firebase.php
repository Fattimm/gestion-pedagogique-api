<?php

return [
    'credentials' => storage_path('firebase/firebase_credentials.json'),
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],
    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],
];
