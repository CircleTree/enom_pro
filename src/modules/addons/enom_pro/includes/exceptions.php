<?php 

/**
 * License Errors
 */
class LicenseExeption extends Exception
{
}
/**
 * cURL / XML Parsing errors
 */
class RemoteException extends Exception
{
    const RETRY_LIMIT = 1;
    const XML_PARSING_EXCEPTION = 2;
    const CURL_EXCEPTION = 3;
}
class WHMCSException extends Exception
{
}