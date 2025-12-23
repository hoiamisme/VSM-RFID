<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Recognition Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk face recognition feature
    |
    */

    // Enable/disable face recognition globally
    'enabled' => env('FACE_RECOGNITION_ENABLED', true),

    // Similarity threshold (0.0 - 1.0)
    // 0.6 = 60% similarity required for match
    'threshold' => env('FACE_SIMILARITY_THRESHOLD', 0.6),

    // Detection timeout in milliseconds
    'detection_timeout' => env('FACE_DETECTION_TIMEOUT', 10000),

    // Maximum retry attempts
    'max_retry' => env('FACE_MAX_RETRY', 3),

    // Model options
    'models' => [
        'path' => env('FACE_MODELS_PATH', '/models'),
        'detector' => env('FACE_DETECTOR_MODEL', 'tiny_face_detector'), // tiny_face_detector or ssd_mobilenetv1
    ],

    // Detection options
    'detection' => [
        'input_size' => 416,  // 128, 160, 224, 320, 416, 512, 608
        'score_threshold' => 0.5, // Confidence threshold
    ],

    // Webcam options
    'webcam' => [
        'width' => 640,
        'height' => 480,
        'facing_mode' => 'user', // user or environment
    ],
];
