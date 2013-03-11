<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Fixture;

use Doctrine\Fixture\Loader\Loader;
use Doctrine\Fixture\Configuration;

/**
 *
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
final class Importer
{
    /**
     * @var \Doctrine\Fixture\Configuration
     */
    private $configuration;

    /**
     * Constructor.
     *
     * @param \Doctrine\Fixture\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Imports fixtures.
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     * @param boolean                         $purge
     */
    public function import(Loader $loader, $purge = false)
    {
        $fixtureList = $this->getSortedFixtureList($loader->load());

        if ($purge) {
            // Purging needs to happen in reverse order of execution
            $this->purgeFixtureList(array_reverse($fixtureList));
        }

        $this->importFixtureList($fixtureList);
    }

    /**
     * Calculate the order for fixtures execution.
     *
     * @param array $fixtureList
     *
     * @return array
     */
    private function getSortedFixtureList(array $fixtureList)
    {
        $calculatorFactory = $this->configuration->getCalculatorFactory();
        $calculator        = $calculatorFactory->getCalculator($fixtureList);

        return $calculator->calculate($fixtureList);
    }

    /**
     * Purges the fixtures.
     *
     * @param array $fixtureList
     *
     * @return void
     */
    private function purgeFixtureList(array $fixtureList)
    {
        $eventManager = $this->configuration->getEventManager();

        foreach ($fixtureList as $fixture) {
            $eventManager->dispatchEvent('purge', new Event\FixtureEvent($fixture));

            $fixture->purge();
        }
    }

    /**
     * Imports the fixtures.
     *
     * @param array $fixtureList
     *
     * @return void
     */
    private function importFixtureList(array $fixtureList)
    {
        $eventManager = $this->configuration->getEventManager();

        foreach ($fixtureList as $fixture) {
            $eventManager->dispatchEvent('import', new Event\FixtureEvent($fixture));

            $fixture->import();
        }
    }
}