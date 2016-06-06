<?php

/**
 * ReportingCloud PHP Wrapper
 *
 * Official wrapper (authored by Text Control GmbH, publisher of ReportingCloud) to access ReportingCloud in PHP.
 *
 * @link      http://www.reporting.cloud to learn more about ReportingCloud
 * @link      https://github.com/TextControl/txtextcontrol-reportingcloud-php for the canonical source repository
 * @license   https://raw.githubusercontent.com/TextControl/txtextcontrol-reportingcloud-php/master/LICENSE.md
 * @copyright © 2016 Text Control GmbH
 */
namespace TxTextControl\ReportingCloud\Filter;

use DateTime;
use DateTimeZone;
use Exception;
use TxTextControl\ReportingCloud\Exception\InvalidArgumentException;

/**
 * TimestampToDateTime filter
 *
 * @package TxTextControl\ReportingCloud
 * @author  Jonathan Maron (@JonathanMaron)
 */
class TimestampToDateTime extends AbstractFilter
{
    /**
     * Convert a UNIX timestamp to the date/time format used by the backend (e.g. "2016-04-15T19:05:18+00:00").
     *
     * @param integer $timestamp UNIX timestamp
     *
     * @return null|string
     */
    public function filter($timestamp)
    {
        $dateTimeString = null;

        if (!is_numeric($timestamp) || $timestamp < 0) {
            throw new InvalidArgumentException(
                sprintf('%s is an invalid unix timestamp integer - it must be numeric and greater than 0',
                    $timestamp)
            );
        }

        try {

            $dateTimeFormat = self::REPORTING_CLOUD_DATE_FORMAT;
            
            $dateTimeZone   = new DateTimeZone(self::REPORTING_CLOUD_TIME_ZONE);
            $dateTime       = new DateTime();

            $dateTime->setTimestamp($timestamp);
            $dateTime->setTimezone($dateTimeZone);

            $dateTimeString = $dateTime->format($dateTimeFormat);

        } catch (Exception $e) {
            throw new InvalidArgumentException(
                sprintf('%s is an invalid unix timestamp integer', $timestamp)
            );
        }

        return $dateTimeString;
    }

}