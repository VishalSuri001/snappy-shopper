<?php

// config/postcode.php

return [
    'download_url' => 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip',
    'csv_dir_path' => 'ukPostcodeZip/Data',
    'chunk_dir_path' => 'ukpostcode/CSVChunks',
    'postcode_csv_path' => 'ukpostcode/postcodes.csv',
];

return [
    'download_url' => env('POSTCODE_DOWNLOAD_URL', 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip'),
    'csv_dir_path' => env('CSV_DIR_PATH', 'ukPostcodeZip/Data'),
    'postcode_csv_path' => env('POSTCODE_CSV_PATH', 'ukpostcode/postcodes.csv'),
    'chunk_dir_path' => env('CHUNK_DIR_PATH', 'ukpostcode/CSVChunks'),
];
