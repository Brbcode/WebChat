<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\BaseContext;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class ExceptionContext extends BaseContext
{
    private KernelInterface $kernel;

    private ?Response $exceptionResponse;

    private JsonInspector $jsonInspector;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->jsonInspector = new JsonInspector('javascript');
    }

    /**
     * @When /^I send fake Request that throw an unhandled exception$/
     */
    public function iSendFakeRequestThatThrowAnUnhandledException()
    {
        $request = new SymfonyRequest();

        $event = new ExceptionEvent($this->kernel, $request, 1, new \Exception());
        $this->kernel->getContainer()->get('event_dispatcher')->dispatch($event, KernelEvents::EXCEPTION);

        $this->exceptionResponse = $event->getResponse();
        if (!$this->exceptionResponse) {
            throw $event->getThrowable();
        }
    }

    /**
     * @Given /^the exception response node "([^"]*)" should be equal to "([^"]*)"$/
     */
    public function theExceptionResponseNodeShouldBeEqualToText($node, $value)
    {
        $this->theExceptionResponseNodeShouldBeEqualTo($node, $value);
    }

    /**
     * @Given /^the exception response node "([^"]*)" should be equal to (\d+)$/
     */
    public function theExceptionResponseNodeShouldBeEqualToNumber($node, $value)
    {
        $this->theExceptionResponseNodeShouldBeEqualTo($node, $value);
    }

    private function theExceptionResponseNodeShouldBeEqualTo($node, $value)
    {
        $json = new Json($this->exceptionResponse->getContent());
        $actual = $this->jsonInspector->evaluate($json, $node);

        if ($actual !== $value) {
            throw new \Exception(
                \sprintf(
                    "The node `%s` value is `%s`",
                    $node,
                    \json_encode($actual, JSON_THROW_ON_ERROR)
                )
            );
        }
    }

    /**
     * @When /^I send fake Request that throw an handled exception with code (\d+) and message:$/
     */
    public function iSendFakeRequestThatThrowAnHandledExceptionWithCodeAndMessage($code, PyStringNode $string)
    {
        $request = new SymfonyRequest();

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            1,
            \App\Exception\Exception::build($string->getRaw(), $code)
        );
        $this->kernel->getContainer()->get('event_dispatcher')->dispatch($event, KernelEvents::EXCEPTION);

        $this->exceptionResponse = $event->getResponse();
        if (!$this->exceptionResponse) {
            throw $event->getThrowable();
        }
    }

    /**
     * @Given /^the exception response node "([^"]*)" should have more than (\d+) elements$/
     */
    public function theExceptionResponseNodeShouldHaveMoreThanElements($node, $count)
    {
        $json = new Json($this->exceptionResponse->getContent());
        $actual = $this->jsonInspector->evaluate($json, $node);

        if (!is_array($actual)) {
            throw new \Exception(
                sprintf("The node `%s` is not an array", $node)
            );
        }

        if (!(count($actual)>$count)) {
            throw new \Exception(
                \sprintf(
                    "The node `%s` expected more than %s elements but only has `%s`",
                    $node,
                    $count,
                    count($actual)
                )
            );
        }
    }

    /**
     * @Given /^the exception response node "([^"]*)" should( not)? exist$/
     */
    public function theExceptionResponseNodeShouldExist($node, $not = false)
    {
        $json = new Json($this->exceptionResponse->getContent());
        try {
            $actual = $this->jsonInspector->evaluate($json, $node);
        } catch (\Exception $e) {
            if (false === $not) {
                throw new \Exception(
                    sprintf("The node `%s` not exist.", $node)
                );
            }
        }

        if (false !== $not) {
            throw new \Exception(
                sprintf("The node `%s` should exist.", $node)
            );
        }
    }

    /**
     * @Given /^(no|\d+) exception (trace(s)?)$/
     */
    public function exceptionTrace($count)
    {
        $count=($count==='no')?0:$count;
        $_ENV['MAXIMUM_TRACE_COUNT'] = $count;
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        unset($_ENV['MAXIMUM_TRACE_COUNT']);
    }

    /**
     * @Given /^the exception response node "([^"]*)" should have( no)? elements$/
     */
    public function theExceptionResponseNodeShouldHaveNoElements($node, $not = false)
    {
        $json = new Json($this->exceptionResponse->getContent());
        $actual = $this->jsonInspector->evaluate($json, $node);

        if (!is_array($actual)) {
            throw new \Exception(
                sprintf("The node `%s` is not an array", $node)
            );
        }

        if (false === $not && count($actual)<=0) {
            throw new \Exception(
                \sprintf(
                    "The node `%s` expected have elements but is empty",
                    $node
                )
            );
        } elseif (false !== $not && count($actual)>0) {
            throw new \Exception(
                \sprintf(
                    "The node `%s` expected have no elements but have %s elements",
                    $node,
                    count($actual)
                )
            );
        }
    }

    /**
     * @Given /^the exception response node "([^"]*)" should have (\d+) elements$/
     */
    public function theExceptionResponseNodeShouldHaveElements($node, $count)
    {
        $json = new Json($this->exceptionResponse->getContent());
        $actual = $this->jsonInspector->evaluate($json, $node);

        if (!is_array($actual)) {
            throw new \Exception(
                sprintf("The node `%s` is not an array", $node)
            );
        }

        if (count($actual)!==$count) {
            throw new \Exception(
                \sprintf(
                    "The node `%s` expected have %s elements but have %s elements",
                    $node,
                    $count,
                    count($actual)
                )
            );
        }
    }
}
