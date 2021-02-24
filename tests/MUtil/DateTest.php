<?php

/**
 *
 * @package    MUtil
 * @subpackage Date
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Date
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class MUtil_DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mutil_Date
     */
    protected $object;

    /**
     * @var Zend_Translate_Adapter
     */
    protected $translate;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * Set up the translation adapter and set locale to 'en'
     */
    protected function setUp()
    {
        date_default_timezone_set('Europe/Amsterdam');
        // date_default_timezone_set('GMT');
        // date_default_timezone_set('America/Los_Angeles');

        // \Zend_Locale::setDefault('en');          // Does not override the browser or system locale
        \Zend_Registry::set('Zend_Locale', 'nl');   // Just set the locale to en in the registry to set a default

        $this->object = new \MUtil_Date();
        $english = array(
            '%s ago' => '%s ago',
            '%s to go' => '%s to go',
            'second' => array(0=>'second',
                              1=>'seconds'),
            'minute' => array(0=>'minute',
                              1=>'minutes'),
            'hour'   => array(0=>'hour',
                              1=>'hours'),
            'day'    => array(0=>'day',
                              1=>'days'),
            'week'   => array(0=>'week',
                              1=>'weeks'),
            'month'  => array(0=>'month',
                              1=>'months'),
            'year'   => array(0=>'year',
                              1=>'years'),
            'decade' => array(0=>'decade',
                              1=>'decades')
        );
        $dutch = array(
            '%s ago' => '%s geleden',
            '%s to go' => 'over %s',
            'second' => array(0=>'seconde',
                              1=>'seconden'),
            'minute' => array(0=>'minuut',
                              1=>'minuten'),
            'hour'   => array(0=>'uur',
                              1=>'uur'),
            'day'    => array(0=>'dag',
                              1=>'dagen'),
            'week'   => array(0=>'week',
                              1=>'weken'),
            'month'  => array(0=>'maand',
                              1=>'maanden'),
            'year'   => array(0=>'jaar',
                              1=>'jaren'),
            'decade' => array(0=>'decennium',
                              1=>'decennia')
        );
        $translate = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => $english,
                'locale'  => 'en'
            )
        );
        $translate->addTranslation(array('content' => $dutch, 'locale' => 'nl'));

        $this->translate = $translate;
        $this->translate->setLocale('en');
    }

    public function providerTestDateTime()
    {
        return array(
            // General case
            array('2016-06-06T00:00:00+02:00', 'c', '2016-06-06 00:00:00', '+0200', 0),
            // Diff dates strings, only timezone is different
            array('2016-06-06T00:00:00+00:00', 'c', '2016-06-06 02:00:00', '+0200', 7200),
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-06T00:00:00', '+00:00', 7200),
            // These one are only correct with: date_default_timezone_set('Europe/Amsterdam');
            array('2016-06-06T00:00:00+02:00', 'c', '2016-06-06 00:00:00', null, 0),
            array('2016-06-06T00:00:00', 'c', '2016-06-06 00:00:00', null, 0),
            // Test timezone in \DateTime string
            array('2016-06-06T00:00:00+04:00', 'c', '2016-06-06 00:00:00+04:00', null, 0),
            // Test different timezone outside \DateTime string is ignored
            array('2016-06-06T00:00:00+04:00', 'c', '2016-06-06 00:00:00+04:00', '+0200', 0),
            // Old date tests for 1901 border
            array('1907-01-01T22:00:00', 'c', '1907-01-01T22:00:00', null, 0),
            array('1905-01-01T22:00:00', 'c', '1905-01-01T22:00:00', null, 0),
            array('1902-01-01T22:00:00', 'c', '1902-01-01T22:00:00', null, 0),
            array('1901-01-01T22:00:00+01:00', 'c', '1901-01-01T22:00:00', '+01:00', 0),
            array('1900-01-01T22:00:00+00:00', 'c', '1900-01-01 22:00:00', '+00:00', 0),
            array('1900-01-01T22:00:00+01:00', 'c', '1900-01-01T22:00:00', '+01:00', 0),
            array('1899-01-01T22:00:00+02:00', 'c', '1899-01-01T22:00:00', '+02:00', 0),
            array('1898-01-01T22:00:00+01:00', 'c', '1898-01-01T22:00:00', '+01:00', 0),
        );
    }

    /**
     * @param string $firstDate
     * @param string $firstFormat
     * @param string $secondDate
     * @param string $timeZone
     * @param int $timeZoneDiff
     *
     * @dataProvider providerTestDateTime
     */
    public function testDateTimeObject($firstDate, $firstFormat, $secondDate, $timeZone, $timeZoneDiff)
    {
        $mDate = new \MUtil_Date($firstDate, $firstFormat);
        if ($timeZone) {
            $pDate = new \DateTime($secondDate, new \DateTimeZone($timeZone));
        } else {
            $pDate = new \DateTime($secondDate);
        }

        if ($pDate->format('Y') > 1901) {
            $this->assertEquals($mDate->getDateTime()->getTimestamp(), $pDate->getTimestamp());
        } else {
            $this->assertEquals($mDate->getDateTime()->format('c'), $pDate->format('c'));
        }
        if ($timeZoneDiff) {
            $this->assertNotEquals($mDate->getDateTime()->getOffset(), $pDate->getOffset());
            $this->assertEquals($timeZoneDiff, abs($mDate->getDateTime()->getOffset() - $pDate->getOffset()));
        } else {
            $this->assertEquals($mDate->getDateTime()->getOffset(), $pDate->getOffset());
        }
    }

    public function providerTestDateWithoutTime()
    {
        return [
            ['2017-05-13 00:00:00', 'yyyy-MM-dd HH:mm:ss', '2017-05-13 00:00:00'],
            ['2017-05-13 00:00', 'yyyy-MM-dd HH:mm', '2017-05-13 00:00:00'],
            ['2017-05-13', 'yyyy-MM-dd', '2017-05-13 00:00:00'],
            ['13-05-2017', 'dd-MM-yyyy', '2017-05-13 00:00:00'],
            ['13-05-2017 00:00', 'dd-MM-yyyy HH:mm', '2017-05-13 00:00:00'],
            ['01-01-1971', 'dd-MM-yyyy', '1971-01-01 00:00:00'],
            ['01-01-1970', 'dd-MM-yyyy', '1970-01-01 00:00:00'],
            ['01-01-1969', 'dd-MM-yyyy', '1969-01-01 00:00:00'],
            ['01-01-1940', 'dd-MM-yyyy', '1940-01-01 00:00:00'],
            ['01-01-1903', 'dd-MM-yyyy', '1903-01-01 00:00:00'],
            ['01-01-1902', 'dd-MM-yyyy', '1902-01-01 00:00:00'],

            // Test unix epoch
            ['14-12-1901', 'dd-MM-yyyy', '1901-12-14 00:00:00'], // Just before 32 bit negative overflow
            ['19-01-2038', 'dd-MM-yyyy', '2038-01-19 00:00:00'], // Just before 32 bit overflow

            ['13-12-1901', 'dd-MM-yyyy', '1901-12-13 00:40:28'], // Zend_Date 1901 and earlier could show a different time due to 32/64 bit implementation
            ['01-06-1900', 'dd-MM-yyyy', '1900-06-01 00:40:28'], // Zend_Date 1901 and earlier could show a different time due to 32/64 bit implementation

            ['20-01-2038', 'dd-MM-yyyy', '2038-01-20 00:00:00'], // Should work as future timezone changes are not present :)
        ];
    }

    /**
     * Test to check if the time is set to 00:00:00 when a date is supplied without time
     *
     * @param $dateString string time e.g. '2017-05-13'
     * @param $dateFormat string Zend format the date is stored in. e.g. 'yyyy-MM-dd'
     * @param $expectedResult string expected string time when converted to yyyy-MM-dd HH:mm:ss
     *
     * @dataProvider providerTestDateWithoutTime
     */
    public function testDateWithoutTime($dateString, $dateFormat, $expectedResult)
    {
        $this->object = new \MUtil_Date($dateString, $dateFormat);
        $year = substr($dateString, strpos($dateFormat, 'yyyy'),4);
        if ($year < 1902) {
            try {
                $this->assertEquals($this->object->toString('yyyy-MM-dd HH:mm:ss'), $expectedResult);
            } catch (\PHPUnit_Framework_ExpectationFailedException $exc) {
                $this->markTestSkipped("Allowed error: Dates before 1902 can be inaccurate on this system:\n" . $exc->getComparisonFailure()->toString());
            }
        } else {
            $this->assertEquals($this->object->toString('yyyy-MM-dd HH:mm:ss'), $expectedResult);
        }
    }

    public function testDiffReadableBeforeAndAfter()
    {
        $this->object = new \MUtil_Date('2010-05-13 12:00:00');
        $testDate = new \MUtil_Date('2010-05-13 12:00:10');
        $this->assertEquals('10 seconds ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 11:59:50');
        $this->assertEquals('10 seconds to go', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 12:00:00');
        $this->assertEquals('0 seconds to go', $this->object->diffReadable($testDate, $this->translate));
    }

    public function testDiffReadableBeforeAndAfterLocalised()
    {
        $this->translate->setLocale('nl');
        $this->object = new \MUtil_Date('2010-05-13 12:00:00');
        $testDate = new \MUtil_Date('2010-05-13 12:00:10');
        $this->assertEquals('10 seconden geleden', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 11:59:50');
        $this->assertEquals('over 10 seconden', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 12:00:00');
        $this->assertEquals('over 0 seconden', $this->object->diffReadable($testDate, $this->translate));
    }

    public function testDiffReadableSingularPlural()
    {
        $this->object = new \MUtil_Date('2010-05-13 12:00:00');
        $testDate = new \MUtil_Date('2010-05-13 12:01:00');
        $this->assertEquals('1 minute ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 12:02:00');
        $this->assertEquals('2 minutes ago', $this->object->diffReadable($testDate, $this->translate));
    }

    public function testDiffReadablePeriods()
    {
        $this->object = new \MUtil_Date('2010-05-13 12:00:00');
        $testDate = new \MUtil_Date('2010-05-13 12:00:01');
        $this->assertEquals('1 second ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 12:01:00');
        $this->assertEquals('1 minute ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-13 13:00:00');
        $this->assertEquals('1 hour ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-14 12:00:00');
        $this->assertEquals('1 day ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-05-20 12:00:00');
        $this->assertEquals('1 week ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2010-06-13 12:00:00');
        $this->assertEquals('1 month ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2011-05-13 12:00:01');
        $this->assertEquals('1 year ago', $this->object->diffReadable($testDate, $this->translate));
        $testDate = new \MUtil_Date('2020-05-13 12:00:00');
        $this->assertEquals('1 decade ago', $this->object->diffReadable($testDate, $this->translate));
    }

    public function testDiffDayDynamic1()
    {
        $date = new \MUtil_Date();

        $this->assertEquals(0, $date->diffDays());
    }

    public function testDiffDayDynamic2()
    {
        $date = new \MUtil_Date();

        $date->setTimeToDayStart();
        $this->assertEquals(0, $date->diffDays());
    }

    public function testDiffDayDynamic3()
    {
        $date = new \MUtil_Date();

        $date->setTimeToDayStart();
        $date->subHour(10);
        $this->assertEquals(-1, $date->diffDays());
    }

    public function providerTestDiffDays()
    {
      return array(
            array('2016-06-06 02:00:00', '2016-06-06 12:34:56', 0),
            array('2016-06-06 10:00:00', '2016-06-07 01:34:56', -1),
            array('2016-06-05 00:00:00', '2016-06-06 12:34:56', -1),
            array('2016-06-06 02:00:00', '2016-06-06 12:34:56', 0),
            array('2016-06-06 01:00:00', '2016-06-06 12:34:56', 0),
            array('2014-06-06 01:00:00', '2015-06-06 12:34:56', -365),
            array('2016-05-06 00:00:00', '2016-06-06 12:34:56', -31),
            array('2016-07-06 00:00:00', '2016-06-06 12:34:56', 30),
            array('2016-06-07 00:00:00', '2016-06-06 12:34:56', 1),
            array('2016-06-06 12:34:57', '2016-06-06 12:34:56', 0),
            array('1902-01-01 12:34:57', '1901-01-01 12:34:56', 365),
        );
    }

    /**
     * @param string $firstDate
     * @param string $secondDate
     * @param int $expectedDiff
     *
     * @dataProvider providerTestDiffDays
     */
    public function testDiffDays($firstDate, $secondDate, $expectedDiff)
    {
        $date = new \MUtil_Date($firstDate);
        $otherDate = new \MUtil_Date($secondDate);
        $diff = $date->diffDays($otherDate);
        $this->assertEquals($expectedDiff, $diff);
    }

    public function providerTestTimeZones()
    {
        return array(
            // These are only correct with: date_default_timezone_set('Europe/Amsterdam');
            array('2016-06-06T00:00:00+02:00', 'c', '2016-06-06 00:00:00', 'yyyy-MM-dd HH:mm:ss', 0, 0),
            array('2016-06-06T00:00:00+02:00', 'c', '2016-06-06 00:01:00', 'yyyy-MM-dd HH:mm:ss', 0, -60),
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-06 02:00:00', 'yyyy-MM-dd HH:mm:ss', 0, 0),
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-06 02:01:00', 'yyyy-MM-dd HH:mm:ss', 0, -60),
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-06 00:00:00', 'yyyy-MM-dd HH:mm:ss', 0, 7200),
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-07 00:00:00', 'yyyy-MM-dd HH:mm:ss', -1, -79200),
            // While the next four times differ only 0 and 1 second, they ARE on a different day according to the date part
            array('2016-06-06T00:00:00+00:00', 'c', '2016-06-05T23:00:00-01:00', 'c', 1, 0),
            array('2016-06-06T00:00:00+00:00', 'c', '2016-06-05T23:00:01-01:00', 'c', 1, -1),
            array('2016-06-05T22:00:00-02:00', 'c', '2016-06-06T02:00:00+02:00', 'c', -1, 0),
            array('2016-06-05T22:00:00-02:00', 'c', '2016-06-06T02:00:01+02:00', 'c', -1, -1),
            // While the next four times differ only 0 and 1 second, they ARE on a different day according to the date part
            array('2016-06-06T23:00:00+00:00', 'c', '2016-06-07T01:00:00+02:00', 'c', -1, 0),
            array('2016-06-06T23:00:01+00:00', 'c', '2016-06-07T01:00:00+02:00', 'c', -1, 1),
            array('2016-06-06T21:00:00-02:00', 'c', '2016-06-07T01:00:00+02:00', 'c', -1, 0),
            array('2016-06-06T21:00:01-02:00', 'c', '2016-06-07T01:00:00+02:00', 'c', -1, 1),
            // The next dates are all actually the same with subtle timezone differences
            array('2016-06-06T02:00:00+02:00', 'c', '2016-06-06T00:00:00+00:00', 'c', 0, 0),
            array('2016-06-06T12:00:00+04:00', 'c', '2016-06-06T08:00:00+00:00', 'c', 0, 0),
            array('2016-06-06T08:00:00+04:00', 'c', '2016-06-06T08:00:00+00:00', 'c', 0, -14400),
            array('2016-06-06T04:00:00+04:00', 'c', '2016-06-06T08:00:00+00:00', 'c', 0, -28800),
            array('2016-06-06T00:00:00-04:00', 'c', '2016-06-06T04:00:00+00:00', 'c', 0, 0),
            // Old date tests for 1901 border
            array('1907-01-01T22:00:00+02:00', 'c', '1905-01-01T22:00:00+02:00', 'c', 730, 63072000),
            array('1902-01-01T22:00:00+02:00', 'c', '1900-01-01T22:00:00+02:00', 'c', 730, 63072000),
            array('1901-01-01T22:00:00+02:00', 'c', '1899-01-01T22:00:00+02:00', 'c', 730, 63072000),
            array('1900-01-01T22:00:00+02:00', 'c', '1898-01-01T22:00:00+02:00', 'c', 730, 63072000),
        );
    }

    /**
     * @param string $firstDate
     * @param string $firstFormat
     * @param string $secondDate
     * @param string $secondFormat
     * @param int $expectedDiffDays
     * @param int $expectedDiffSeconds
     *
     * @dataProvider providerTestTimeZones
     */
    public function testDiffTimeZones($firstDate, $firstFormat, $secondDate, $secondFormat, $expectedDiffDays, $expectedDiffSeconds)
    {
        $date = new \MUtil_Date($firstDate, $firstFormat);
        $otherDate = new \MUtil_Date($secondDate, $secondFormat);
        $diff = $date->diffDays($otherDate);
        // echo $secondFormat . ' ' . $date->getTimestamp() . ' - ' . $date->getGmtOffset() . ' vs  ' . $otherDate->getTimestamp() . ' - ' . $otherDate->getGmtOffset() . "\n";
        $this->assertEquals($expectedDiffDays, $diff);
        $this->assertEquals($expectedDiffSeconds, $date->diffSeconds($otherDate));
    }

    public function testIsEarlier()
    {
        $now = new Mutil_Date();
        $yesterday = clone $now;
        $yesterday = $yesterday->subDay(1);
        $tomorrow =  clone $now;
        $tomorrow = $tomorrow->addDay(1);

        $this->assertEquals(false, $now->isEarlier($now), 'does not pickup equal');
        $this->assertEquals(false, $now->isEarlier($yesterday), 'does not pickup earlier');
        $this->assertEquals(true, $now->isEarlier($tomorrow), 'does not pickup later');
    }

    public function testIsEarlierorEqual()
    {
        $now = new Mutil_Date();
        $yesterday = clone $now;
        $yesterday = $yesterday->subDay(1);
        $tomorrow =  clone $now;
        $tomorrow = $tomorrow->addDay(1);

        $this->assertEquals(true, $now->isEarlierOrEqual($now), 'does not pickup equal');
        $this->assertEquals(false, $now->isEarlierOrEqual($yesterday), 'does not pickup earlier');
        $this->assertEquals(true, $now->isEarlierOrEqual($tomorrow), 'does not pickup later');
    }

    public function testIsLater()
    {
        $now = new Mutil_Date();
        $yesterday = clone $now;
        $yesterday = $yesterday->subDay(1);
        $tomorrow =  clone $now;
        $tomorrow = $tomorrow->addDay(1);

        $this->assertEquals(false, $now->isLater($now), 'does not pickup equal');
        $this->assertEquals(true, $now->isLater($yesterday), 'does not pickup earlier');
        $this->assertEquals(false, $now->isLater($tomorrow), 'does not pickup later');
    }

    public function testIsLaterorEqual()
    {
        $now = new Mutil_Date();
        $yesterday = clone $now;
        $yesterday = $yesterday->subDay(1);
        $tomorrow =  clone $now;
        $tomorrow = $tomorrow->addDay(1);

        $this->assertEquals(true, $now->isLaterOrEqual($now), 'does not pickup equal');
        $this->assertEquals(true, $now->isLaterOrEqual($yesterday), 'does not pickup earlier');
        $this->assertEquals(false, $now->isLaterOrEqual($tomorrow), 'does not pickup later');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }
}