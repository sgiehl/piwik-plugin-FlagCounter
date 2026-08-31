<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\FlagCounter\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Tracks a known number of visits per country, with a deliberate tie and one
 * visit whose country cannot be resolved, so the counter's ordering and its
 * handling of unknown countries can both be asserted.
 */
class CountryVisits extends Fixture
{
    public $dateTime = '2018-03-04 00:00:00';
    public $idSite = 1;

    /**
     * Country code => number of visits. 'de' and 'fr' are unique so the first two
     * positions are deterministic; 'us' and 'jp' tie so the ordering below them is not
     * asserted. The unknown visit is tracked separately.
     */
    public const VISITS_PER_COUNTRY = [
        'de' => 4,
        'fr' => 3,
        'us' => 2,
        'jp' => 2,
    ];

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->trackVisits();
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function trackVisits()
    {
        $tracker = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $tracker->setTokenAuth(self::getTokenAuth());

        $visit = 0;

        foreach (self::VISITS_PER_COUNTRY as $country => $visits) {
            for ($i = 0; $i < $visits; $i++) {
                $this->prepareVisit($tracker, ++$visit);
                $tracker->setCountry($country);

                self::checkResponse($tracker->doTrackPageView('Page viewed from ' . $country));
            }
        }

        // a tracker without a country, so the visit is attributed to the unknown country
        $unknown = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $unknown->setTokenAuth(self::getTokenAuth());
        $this->prepareVisit($unknown, ++$visit);

        self::checkResponse($unknown->doTrackPageView('Page viewed from an unknown country'));
    }

    private function prepareVisit($tracker, int $visit): void
    {
        $tracker->setVisitorId(sprintf('%016x', $visit));
        $tracker->setForceVisitDateTime(
            Date::factory($this->dateTime)->addHour($visit)->getDatetime()
        );
        $tracker->setForceNewVisit();
    }
}
