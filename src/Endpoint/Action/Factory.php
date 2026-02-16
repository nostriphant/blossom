<?php

namespace nostriphant\Blossom\Endpoint\Action;

interface Factory {
    
    public function __invoke(\nostriphant\Blossom\HTTP\ServerRequest $request) : \nostriphant\Blossom\Endpoint\Action;
    
}
