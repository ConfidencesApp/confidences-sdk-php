<?php

// Confidences singleton
require dirname(__FILE__) . '/src/Confidences/Confidences.php';

// Utilities
require dirname(__FILE__) . '/src/Confidences/Util/Util.php';

// HttpClient
require dirname(__FILE__) . '/src/Confidences/HttpClient/ClientInterface.php';
require dirname(__FILE__) . '/src/Confidences/HttpClient/CurlClient.php';

// Exceptions
require dirname(__FILE__) . '/src/Confidences/Exception/BaseException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/ApiConnectionException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/ApiException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/AuthenticationException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/AuthorizationException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/CreditException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/InvalidRequestException.php';
require dirname(__FILE__) . '/src/Confidences/Exception/UniqueResponseException.php';

// Plumbing
require dirname(__FILE__) . '/src/Confidences/Api/ApiRequestor.php';
require dirname(__FILE__) . '/src/Confidences/Api/ApiResponse.php';

// Confidences API Resources
require dirname(__FILE__) . '/src/Confidences/Survey.php';
