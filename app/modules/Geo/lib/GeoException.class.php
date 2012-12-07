<?php

class GeoException extends Exception
{
    const GOOGLE_COMPONENTS_FORMAT = 1;
    const INVALID_BACKEND_RESPONSE = 2;
    const NETWORK_ERROR = 3;
    const INVALID_RESULT_SECTION = 4;
    const INVALID_RESULT_VALUE = 5;
    const INTERNAL_ERROR = 6;
}