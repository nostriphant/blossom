<?php

namespace nostriphant\Blossom;

class UploadConstraints {
    
    public function __construct(
            public ?array $allowed_pubkeys = [],
            public ?int $max_upload_size = null,
            public array $unsupported_content_types = []
    ) {
        
    }
    
    public function __invoke(string $pubkey_hex, int $content_length, string $content_type, callable $authorized, callable $unauthorized) : array {
        if (isset($this->allowed_pubkeys)) {
            if (in_array($pubkey_hex, $this->allowed_pubkeys) === false) {
                return $unauthorized(401, 'Pubkey "' . $pubkey_hex . '" is not allowed to upload files');
            }
        }

        if (isset($this->max_upload_size)) {
            if ($content_length > $this->max_upload_size) {
                return $unauthorized(413, 'File too large. Max allowed size is '.$this->max_upload_size.' bytes.');
            }
        }

        if (isset($this->unsupported_content_types)) {
            if (isset($this->unsupported_content_types) === false) {
            } elseif (in_array($content_type, $this->unsupported_content_types)) {
                return $unauthorized(415, 'Unsupported file type "' . $content_type . '".');
            }

            foreach (array_filter($this->unsupported_content_types, fn(string $unsupported_content_type) => str_ends_with($unsupported_content_type, '/*')) as $unsupported_content_type) {
                list($category, $type) = explode('/', $unsupported_content_type, 2);
                if (str_starts_with($content_type, $category . '/')) {
                    return $unauthorized(415, 'Unsupported file type "' . $content_type . '".');
                }
            }
        }
        return $authorized();
    }
}
