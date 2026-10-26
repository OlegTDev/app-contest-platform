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

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for auto-generated image thumbnails.
    |
    */

    'thumbnail' => [
        /*
        | Maximum thumbnail dimension in pixels (square box).
        | Images are proportionally fitted within this size.
        */
        'max_size' => (int) env('THUMBNAIL_MAX_SIZE', 600),

        /*
        | JPEG quality: 1 (worst) to 100 (best).
        | Recommended: 85-95. Higher = better quality, larger file.
        */
        'quality' => (int) env('THUMBNAIL_QUALITY', 95),

        /*
        | Use two-pass resize for smoother downscaling.
        | True gives noticeably better quality for large images.
        */
        'two_pass' => (bool) env('THUMBNAIL_TWO_PASS', true),
    ],

];
