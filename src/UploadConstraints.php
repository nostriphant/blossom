<?php

namespace nostriphant\Blossom;

class UploadConstraints {
    
    public function __construct(
            public ?array $allowed_pubkeys = null,
            public ?int $max_upload_size = null
    ) {
        
    }
    
}
