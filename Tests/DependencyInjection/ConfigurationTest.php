<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\DependencyInjection;

use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The minimal, required config needed to not have any required validation
     * issues.
     *
     * @var array
     */
    protected static $minimalConfig = array(
        'duo' => array(
            'ikey' => 'integration_key',
            'skey' => 'secret_key',
            'akey' => 'application_key',
            'host' => 'api-XXXXXXXX.duosecurity.com'
        )
    );

    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    public function tearDown()
    {
        $this->configuration = null;
    }

    public function testMinimalConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration($this->configuration, array(self::$minimalConfig));

        $this->assertEquals(self::$minimalConfig, $config);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testNoConfigForDuo()
    {
        $config = array(
            'duo' => null
        );

        $processor = new Processor();
        $config = $processor->processConfiguration($this->configuration, array($config));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testManyConfigForDuo()
    {
        $config = array_merge(
            self::$minimalConfig,
            array(
                'duo' => array(
                    'lots_o_keys' => null
                )
            )
        );

        $processor = new Processor();
        $config = $processor->processConfiguration($this->configuration, array($config));
    }
}
