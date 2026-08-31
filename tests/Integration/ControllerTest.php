<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\FlagCounter\tests\Integration;

use Piwik\Cache;
use Piwik\Plugins\FlagCounter\Controller;
use Piwik\Plugins\FlagCounter\SystemSettings;
use Piwik\Plugins\FlagCounter\tests\Fixtures\CountryVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Exposes the controller internals the counter is built on.
 */
class ControllerUnderTest extends Controller
{
    public function callGetCountryData(): array
    {
        return $this->getCountryData();
    }

    public function callGetCacheKey($idSite, $period, $date): string
    {
        return $this->getCacheKey($idSite, $period, $date);
    }

    public function callGetCacheLifetime(): int
    {
        return $this->getCacheLifetime();
    }
}

/**
 * @group Plugins
 * @group FlagCounter
 * @group FlagCounterControllerTest
 */
class ControllerTest extends SystemTestCase
{
    /** @var CountryVisits */
    public static $fixture;

    /** @var ControllerUnderTest */
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUnderTest();

        $_GET['idSite'] = self::$fixture->idSite;
        $_GET['period'] = 'day';
        $_GET['date']   = '2018-03-04';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['period'], $_GET['date']);
        Cache::flushAll();

        parent::tearDown();
    }

    public function testGetCountryDataReturnsOneEntryPerCountry()
    {
        $countries = $this->controller->callGetCountryData();

        // the four tracked countries plus the visit that could not be resolved
        $this->assertCount(count(CountryVisits::VISITS_PER_COUNTRY) + 1, $countries);
    }

    public function testGetCountryDataSortsByHitsDescending()
    {
        $countries = $this->controller->callGetCountryData();

        $hits = array_column($countries, 'hits');
        $sorted = $hits;
        rsort($sorted);

        $this->assertSame($sorted, $hits);
    }

    public function testGetCountryDataReportsTheTrackedNumberOfVisits()
    {
        $countries = $this->controller->callGetCountryData();

        $hitsByCode = array_combine(array_column($countries, 'code'), array_column($countries, 'hits'));

        foreach (CountryVisits::VISITS_PER_COUNTRY as $code => $expectedVisits) {
            $this->assertArrayHasKey($code, $hitsByCode, "no row for country $code");
            $this->assertEquals($expectedVisits, $hitsByCode[$code], "wrong visit count for country $code");
        }
    }

    public function testGetCountryDataReplacesTheUnknownCountryCodeWithBlanks()
    {
        $countries = $this->controller->callGetCountryData();

        $codes = array_column($countries, 'code');

        $this->assertNotContains('xx', $codes, 'the unknown country code leaked into the output');
        $this->assertContains('  ', $codes, 'the unknown country was not replaced with blanks');
    }

    public function testGetCountryDataReturnsANameAndFlagForEveryCountry()
    {
        $countries = $this->controller->callGetCountryData();

        foreach ($countries as $country) {
            $this->assertNotEmpty($country['name'], 'a country is missing its name');
            $this->assertNotEmpty($country['icon'], 'a country is missing its flag');
            $this->assertFileExists(
                PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $country['icon'],
                'the flag the counter would draw does not exist: ' . $country['icon']
            );
        }
    }

    public function testGetCountryDataServesRepeatedCallsFromTheCache()
    {
        $first = $this->controller->callGetCountryData();

        $cache = Cache::getLazyCache();
        $key   = $this->controller->callGetCacheKey($_GET['idSite'], $_GET['period'], $_GET['date']);

        $this->assertTrue($cache->contains($key), 'the country data was not cached');
        $this->assertSame($first, $cache->fetch($key));

        // a changed cache entry must be served as-is, proving the second call does not re-query
        $cache->save($key, [['name' => 'Cached', 'icon' => '', 'code' => 'zz', 'hits' => 1]], 3600);

        $second = $this->controller->callGetCountryData();

        $this->assertSame('Cached', $second[0]['name']);
    }

    public function testCacheKeyIsUniquePerSitePeriodAndDate()
    {
        $key = $this->controller->callGetCacheKey(1, 'day', '2018-03-04');

        $this->assertNotSame($key, $this->controller->callGetCacheKey(2, 'day', '2018-03-04'));
        $this->assertNotSame($key, $this->controller->callGetCacheKey(1, 'week', '2018-03-04'));
        $this->assertNotSame($key, $this->controller->callGetCacheKey(1, 'day', '2018-03-05'));
    }

    public function testCacheKeyDropsCharactersThatAreNotSafeForACacheId()
    {
        // date ranges and segments arrive with dashes, commas and colons in them
        $key = $this->controller->callGetCacheKey(1, 'range', '2018-03-04,2018-03-11');

        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/i', $key);
    }

    public function testCacheLifetimeFallsBackToAnHourWhenTheSettingIsEmpty()
    {
        $settings = new SystemSettings();
        $settings->cacheLifeTime->setValue(0);
        $settings->save();

        $this->assertSame(3600, $this->controller->callGetCacheLifetime());
    }

    public function testCacheLifetimeUsesTheConfiguredValue()
    {
        $settings = new SystemSettings();
        $settings->cacheLifeTime->setValue(60);
        $settings->save();

        $this->assertSame(60, $this->controller->callGetCacheLifetime());
    }
}

ControllerTest::$fixture = new CountryVisits();
