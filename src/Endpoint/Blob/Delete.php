<?php

namespace nostriphant\Blossom\Endpoint\Blob;


readonly class Delete implements \nostriphant\Blossom\Endpoint\Action {
    public function __construct(private \nostriphant\Blossom\Blob $blob) {}
    
    public function authorize(\nostriphant\NIP01\Event $authorization_event) : bool {
        return true;
    }
    
    public function __invoke() : array {
        if ($this->blob->exists === false) {
            return ['status' => 200];
        }
        
        \nostriphant\Blossom\Blob::delete($this->blob);
        
        return ['status' =>  204];
    }
}
