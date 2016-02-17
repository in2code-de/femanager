<?php
use Behat\MinkExtension\Context\MinkContext;

/**
 * Class FeatureContext
 */
class FeatureContext extends MinkContext
{

    /**
     * @var array
     */
    protected $variables;

    /**
     * Wait for X seconds
     *
     * @Given /^I wait "([^"]*)" seconds$/
     *
     * @param string|int $seconds
     * @return void
     */
    public function iWaitSeconds($seconds)
    {
        if (!is_numeric($seconds)) {
            $seconds = 10;
        }
        sleep($seconds);
    }

    /**
     * Search for this string in html sourcecode
     *
     * @Then /^the sourcecode should contain \'([^\']*)\'$/
     *
     * @param string $html
     * @return void
     */
    public function theSourcecodeShouldContain($html)
    {
        $html = str_replace('\n', PHP_EOL, $html);
        $this->assertSession()->responseContains($this->fixStepArgument($html));
    }

    /**
     * Search for this string in html sourcecode
     *
     * @Then /^the sourcecode should not contain \'([^\']*)\'$/
     *
     * @param string $html
     * @return void
     */
    public function theSourcecodeShouldNotContain($html)
    {
        $html = str_replace('\n', PHP_EOL, $html);
        $this->assertSession()->responseNotContains($this->fixStepArgument($html));
    }

    /**
     * Override MinkContext::fixStepArgument().
     *      Make it possible to use [random].
     *      If you want to use the previous random value [random:1].
     *
     * @param string $argument
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        $argument = str_replace('\"', '"', $argument);

        // Token replace the argument.
        static $random = [];
        for ($start = 0; ($start = strpos($argument, '[', $start)) !== false;) {
            $end = strpos($argument, ']', $start);
            if ($end === false) {
                break;
            }
            $name = substr($argument, $start + 1, $end - $start - 1);
            if ($name === 'random') {
                $this->variables[$name] = $this->createRandomString(12);
                $random[] = $this->variables[$name];
            } elseif (substr($name, 0, 7) === 'random:') {
                // In order to test previous random values stored in the form,
                // suppport random:n, where n is the number or random's ago
                // to use, i.e., random:1 is the previous random value.
                $num = substr($name, 7);
                if (is_numeric($num) && $num <= count($random)) {
                    $this->variables[$name] = $random[count($random) - $num];
                }
            }
            if (isset($this->variables[$name])) {
                $argument = substr_replace($argument, $this->variables[$name], $start, $end - $start + 1);
                $start += strlen($this->variables[$name]);
            } else {
                $start = $end + 1;
            }
        }

        return $argument;
    }

    /**
     * createRandomFileName
     *
     * @param int $length
     * @param bool $lowerAndUpperCase
     * @return string
     */
    protected function createRandomString($length = 32, $lowerAndUpperCase = false)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        if ($lowerAndUpperCase) {
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $fileName = '';
        for ($i = 0; $i < $length; $i++) {
            $key = mt_rand(0, strlen($characters) - 1);
            $fileName .= $characters[$key];
        }
        return $fileName;
    }
}
