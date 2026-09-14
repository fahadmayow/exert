<?php

return [
    // 1xx Informational
    '100' => [
        'status'  => 'CONTINUE',
        'code'    => 100,
        'message' => 'The server has received the request headers and the client should proceed to send the request body.',
    ],
    '101' => [
        'status'  => 'SWITCHING PROTOCOLS',
        'code'    => 101,
        'message' => 'The requester has asked the server to switch protocols and the server has agreed to do so.',
    ],
    '102' => [
        'status'  => 'PROCESSING',
        'code'    => 102,
        'message' => 'The server has received and is processing the request, but no response is available yet.',
    ],

    // 2xx Success
    '200' => [
        'status'  => 'OK',
        'code'    => 200,
        'message' => 'The request was successful.',
    ],
    '201' => [
        'status'  => 'CREATED',
        'code'    => 201,
        'message' => 'The resource was successfully created.',
    ],
    '202' => [
        'status'  => 'ACCEPTED',
        'code'    => 202,
        'message' => 'The request has been accepted for processing, but the processing has not been completed.',
    ],
    '203' => [
        'status'  => 'NON-AUTHORITATIVE INFORMATION',
        'code'    => 203,
        'message' => 'The request was successful but the enclosed payload has been modified from that of the origin server\'s 200 OK response.',
    ],
    '204' => [
        'status'  => 'NO CONTENT',
        'code'    => 204,
        'message' => 'The server successfully processed the request and is not returning any content.',
    ],
    '205' => [
        'status'  => 'RESET CONTENT',
        'code'    => 205,
        'message' => 'The server successfully processed the request, but is not returning any content, and requires that the requester reset the document view.',
    ],
    '206' => [
        'status'  => 'PARTIAL CONTENT',
        'code'    => 206,
        'message' => 'The server is delivering only part of the resource due to a range header sent by the client.',
    ],

    // 3xx Redirection
    '300' => [
        'status'  => 'MULTIPLE CHOICES',
        'code'    => 300,
        'message' => 'The request has more than one possible response.',
    ],
    '301' => [
        'status'  => 'MOVED PERMANENTLY',
        'code'    => 301,
        'message' => 'The requested resource has been assigned a new permanent URI.',
    ],
    '302' => [
        'status'  => 'FOUND',
        'code'    => 302,
        'message' => 'The requested resource resides temporarily under a different URI.',
    ],
    '303' => [
        'status'  => 'SEE OTHER',
        'code'    => 303,
        'message' => 'The response to the request can be found under another URI using the GET method.',
    ],
    '304' => [
        'status'  => 'NOT MODIFIED',
        'code'    => 304,
        'message' => 'The resource has not been modified since the version specified by the request headers.',
    ],
    '305' => [
        'status'  => 'USE PROXY',
        'code'    => 305,
        'message' => 'The requested resource is available only through a proxy.',
    ],
    '307' => [
        'status'  => 'TEMPORARY REDIRECT',
        'code'    => 307,
        'message' => 'The requested resource resides temporarily under a different URI.',
    ],
    '308' => [
        'status'  => 'PERMANENT REDIRECT',
        'code'    => 308,
        'message' => 'The requested resource has been assigned a new permanent URI.',
    ],

    // 4xx Client Error
    '400' => [
        'status'  => 'BAD REQUEST',
        'code'    => 400,
        'message' => 'The server cannot or will not process the request due to an apparent client error.',
    ],
    '401' => [
        'status'  => 'UNAUTHORIZED',
        'code'    => 401,
        'message' => 'The request requires user authentication.',
    ],
    '402' => [
        'status'  => 'PAYMENT REQUIRED',
        'code'    => 402,
        'message' => 'Payment is required to access the requested resource.',
    ],
    '403' => [
        'status'  => 'FORBIDDEN',
        'code'    => 403,
        'message' => 'Access is denied. You dont have the right permission to perform this request!. ',
    ],
    '404' => [
        'status'  => 'NOT FOUND',
        'code'    => 404,
        'message' => 'The requested resource could not be found.',
    ],
    '405' => [
        'status'  => 'METHOD NOT ALLOWED',
        'code'    => 405,
        'message' => 'The method specified in the request is not allowed for the resource identified by the request URI.',
    ],
    '406' => [
        'status'  => 'NOT ACCEPTABLE',
        'code'    => 406,
        'message' => 'The resource identified by the request is only capable of generating response entities which have content characteristics not acceptable according to the accept headers sent in the request.',
    ],
    '407' => [
        'status'  => 'PROXY AUTHENTICATION REQUIRED',
        'code'    => 407,
        'message' => 'The client must first authenticate itself with the proxy.',
    ],
    '408' => [
        'status'  => 'REQUEST TIMEOUT',
        'code'    => 408,
        'message' => 'The client did not produce a request within the time that the server was prepared to wait.',
    ],
    '409' => [
        'status'  => 'CONFLICT',
        'code'    => 409,
        'message' => 'The request could not be completed due to a conflict with the current state of the resource.',
    ],
    '410' => [
        'status'  => 'GONE',
        'code'    => 410,
        'message' => 'The requested resource is no longer available at the server and no forwarding address is known.',
    ],
    '411' => [
        'status'  => 'LENGTH REQUIRED',
        'code'    => 411,
        'message' => 'The server refuses to accept the request without a defined Content-Length.',
    ],
    '412' => [
        'status'  => 'PRECONDITION FAILED',
        'code'    => 412,
        'message' => 'The precondition given in one or more of the request-header fields evaluated to false when it was tested on the server.',
    ],
    '413' => [
        'status'  => 'PAYLOAD TOO LARGE',
        'code'    => 413,
        'message' => 'The server is refusing to process a request because the request payload is larger than the server is willing or able to process.',
    ],
    '414' => [
        'status'  => 'URI TOO LONG',
        'code'    => 414,
        'message' => 'The server is refusing to service the request because the request-target is longer than the server is willing to interpret.',
    ],
    '415' => [
        'status'  => 'UNSUPPORTED MEDIA TYPE',
        'code'    => 415,
        'message' => 'The origin server is refusing to service the request because the payload is in a format not supported by the target resource for this method.',
    ],
    '416' => [
        'status'  => 'RANGE NOT SATISFIABLE',
        'code'    => 416,
        'message' => 'The range specified by the Range header field in the request cannot be fulfilled.',
    ],
    '417' => [
        'status'  => 'EXPECTATION FAILED',
        'code'    => 417,
        'message' => 'The expectation given in an Expect request-header field could not be met by this server.',
    ],
    '422' => [
        'status'  => 'UNPROCESSABLE ENTITY',
        'code'    => 422,
        'message' => 'The server understands the content type of the request entity, and the syntax of the request entity is correct, but was unable to process the contained instructions.',
    ],
    '429' => [
        'status'  => 'TOO MANY REQUESTS',
        'code'    => 429,
        'message' => 'The user has sent too many requests in a given amount of time.',
    ],

    // 5xx Server Error
    '500' => [
        'status'  => 'INTERNAL SERVER ERROR',
        'code'    => 500,
        'message' => 'The server encountered an unexpected condition which prevented it from fulfilling the request.',
    ],
    '501' => [
        'status'  => 'NOT IMPLEMENTED',
        'code'    => 501,
        'message' => 'The server does not support the functionality required to fulfill the request.',
    ],
    '502' => [
        'status'  => 'BAD GATEWAY',
        'code'    => 502,
        'message' => 'The server, while acting as a gateway or proxy, received an invalid response from the upstream server it accessed in attempting to fulfill the request.',
    ],
    '503' => [
        'status'  => 'SERVICE UNAVAILABLE',
        'code'    => 503,
        'message' => 'The server is currently unable to handle the request due to a temporary overloading or maintenance of the server.',
    ],
    '504' => [
        'status'  => 'GATEWAY TIMEOUT',
        'code'    => 504,
        'message' => 'The server, while acting as a gateway or proxy, did not receive a timely response from the upstream server specified by the URI.',
    ],
    '505' => [
        'status'  => 'HTTP VERSION NOT SUPPORTED',
        'code'    => 505,
        'message' => 'The server does not support, or refuses to support, the HTTP protocol version that was used in the request message.',
    ],
];
