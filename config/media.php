<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload File Size (in KB)
    |--------------------------------------------------------------------------
    |
    | Maximum size allowed for a single uploaded file. Default is 100 MB.
    |
    */

    'max_upload_size' => (int) env('MAX_UPLOAD_SIZE', 102400),

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions
    |--------------------------------------------------------------------------
    |
    | List of allowed file extensions for upload.
    |
    */

    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp',  // images
        'mp4', 'mov', 'avi', 'mkv', 'webm',    // videos
        'pdf',                                 // documents
        'doc', 'docx', 'xls', 'xlsx',          // office
        'zip', 'rar',                          // archives
    ],

];
