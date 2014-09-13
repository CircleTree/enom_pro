<?php 

/**
 * License Errors
 */
class LicenseException extends Exception
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
/**
 * WHMCS API Exceptions
 */
class WHMCSException extends Exception
{
}
/**
 * Missing dependency exception 
 */
class MissingDependencyException extends Exception 
{
}