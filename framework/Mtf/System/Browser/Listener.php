<?php
/**
 * {license_notice}
 *
 * @api
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Mtf\System\Browser;

use Exception;
use Mtf\Factory\Factory;
use Mtf\System\Config;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;

/**
 * Class Listener.
 * This listener provides strategy of reopening browser according reopen_browser_on config.
 *
 * @package Mtf\System\Browser
 */
class Listener implements \PHPUnit_Framework_TestListener
{
    /**
     * Scope
     */
    const SCOPE_TEST = 'test';
    const SCOPE_TEST_CASE = 'testCase';

    /**
     * Current scope
     *
     * @var string
     */
    protected $_scope;

    /**
     * @constructor
     * @param Config $configuration
     */
    public function __construct(Config $configuration = null)
    {
        if (!isset($configuration)) {
            $configuration = new Config();
        }
        $this->_scope = $configuration->getConfigParam('application/reopen_browser_on') ?: static::SCOPE_TEST_CASE;
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (class_exists($suite->getName()) && is_subclass_of($suite->getName(), '\\PHPUnit_Framework_TestCase')) {
            $this->_run(static::SCOPE_TEST_CASE);
        }
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     * Reopen browser before every test if it was terminated.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($this->_isBrowserFailed()) {
            $this->_reopenBrowser();
        } else {
            $this->_run(static::SCOPE_TEST);
        }
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }

    /**
     * Reopen browser for current scope.
     * Reopening is skipped for first time.
     *
     * @param string $scope
     */
    protected function _run($scope)
    {
        if ($scope != $this->_scope) {
            return;
        }
        static $runCounter = 0;
        if (0 < $runCounter++) {
            $this->_reopenBrowser();
        }
    }

    /**
     * Validate if browser was terminated
     *
     * @return bool
     */
    protected function _isBrowserFailed()
    {
        try {
            $browser = Factory::getClientBrowser();
            //If browser was terminated every browser method call will throw specific exception
            $browser->getUrl();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            return true;
        }
        return false;
    }

    /**
     * Force reopen browser
     */
    protected function _reopenBrowser()
    {
        Factory::getClientBrowser()->reopen();
    }
}
