<?php

namespace nostriphant\Blossom\Endpoint\Upload;

use \nostriphant\Functional\Partial;

readonly class Put implements \nostriphant\Blossom\Endpoint\Action {

    private \Closure $upload_authorized;
    
    public function __construct(callable $upload_authorized, private \nostriphant\Blossom\Blob\Uncreated $blob, private mixed $stream) {
        $this->upload_authorized = \Closure::fromCallable($upload_authorized);
    }
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event, array $additional_headers, callable $action, callable $unauthorized) : array {
        return $action(Partial::right($this->upload_authorized,
                $additional_headers['CONTENT_LENGTH'] ?? -1, 
                $additional_headers['CONTENT_TYPE'] ?? "application/octet-stream", 
                Partial::right($this->blob, $this->stream, \nostriphant\NIP01\Event::extractTagValues($authorization_event, 'x')[0][0]),
                $unauthorized
        ));
    }
}
