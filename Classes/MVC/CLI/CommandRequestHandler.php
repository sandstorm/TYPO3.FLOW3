<?php
namespace TYPO3\FLOW3\MVC\CLI;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use TYPO3\FLOW3\Core\Bootstrap;

/**
 * A request handler which can handle command line requests.
 *
 * @FLOW3\Proxy(false)
 * @FLOW3\Scope("singleton")
 */
class CommandRequestHandler implements \TYPO3\FLOW3\Core\RequestHandlerInterface {

	/**
	 * @var \TYPO3\FLOW3\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var \TYPO3\FLOW3\Cli\Request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap
	 * @return void
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * This request handler can handle CLI requests.
	 *
	 * @return boolean If the request is a CLI request, TRUE otherwise FALSE
	 */
	public function canHandleRequest() {
		return (PHP_SAPI === 'cli');
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler.
	 */
	public function getPriority() {
		return 100;
	}

	/**
	 * Handles a command line request
	 *
	 * @return void
	 */
	public function handleRequest() {
		try {
			if ($this->bootstrap->isCompiletimeCommand(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '')) {
				$this->handleCompiletimeCommand();
			} else {
				$this->handleRuntimeCommand();
			}
		} catch (\Exception $exception) {
			$this->handleException($exception);
		}
	}

	/**
	 * Returns the top level request built by the request handler.
	 *
	 * In most cases the dispatcher or other parts of the request-response chain
	 * should be preferred for retrieving the current request, because sub requests
	 * or simulated requests are built later in the process.
	 *
	 * If, however, the original top level request is wanted, this is the right
	 * method for getting it.
	 *
	 * @return \TYPO3\FLOW3\MVC\RequestInterface The originally built web request
	 * @api
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Specialized handler for compiletime commands
	 *
	 * Builds and executes a boot sequences for bringing FLOW3 into compiletime and
	 * then dispatches the command as specified in the command line.
	 *
	 * @return void
	 */
	protected function handleCompiletimeCommand() {
		$sequence = $this->bootstrap->buildCompiletimeSequence();
		$sequence->invoke($this->bootstrap);

		$commandLine = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
		$objectManager = $this->bootstrap->getObjectManager();

		$this->request = $objectManager->get('TYPO3\FLOW3\MVC\CLI\RequestBuilder')->build(array_slice($commandLine, 1));
		$response = new \TYPO3\FLOW3\MVC\CLI\Response();
		$objectManager->get('TYPO3\FLOW3\MVC\Dispatcher')->dispatch($this->request, $response);

		$response->send();
		$this->bootstrap->shutdown('Compiletime');

		$objectManager->get('TYPO3\FLOW3\Core\LockManager')->unlockSite();
		exit($response->getExitCode());
	}

	/**
	 * Specialized handler for runtime commands
	 *
	 * Builds and executes a boot sequences for bringing FLOW3 into runtime and
	 * then dispatches the command as specified in the command line.
	 *
	 * @return void
	 */
	protected function handleRuntimeCommand() {
		$sequence = $this->bootstrap->buildRuntimeSequence();
		$sequence->invoke($this->bootstrap);

		$objectManager = $this->bootstrap->getObjectManager();

		$commandLine = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
		$this->request = $objectManager->get('TYPO3\FLOW3\MVC\CLI\RequestBuilder')->build(array_slice($commandLine, 1));
		$command = $this->request->getCommand();
		if ($this->bootstrap->isCompiletimeCommand($command->getCommandIdentifier())) {
			throw new \TYPO3\FLOW3\MVC\Exception\InvalidCommandIdentifierException(sprintf('The command "%s" must be specified by its full command identifier because it is a compile time command which cannot be resolved from an abbreviated command identifier.', $command->getCommandIdentifier()), 1310992499);
		}
		$response = new \TYPO3\FLOW3\MVC\CLI\Response();
		$objectManager->get('TYPO3\FLOW3\MVC\Dispatcher')->dispatch($this->request, $response);

		$response->send();
		$this->bootstrap->shutdown('Runtime');
		exit($response->getExitCode());
	}

	/**
	 * Displays a human readable, partly beautified version of the given exception
	 * and stops the application, return a non-zero exit code.
	 *
	 * @param \Exception $exception
	 * @return void
	 */
	protected function handleException(\Exception $exception) {
		$response = new \TYPO3\FLOW3\MVC\CLI\Response();

		$exceptionMessage = '';
		$exceptionReference = "\n<b>More Information</b>\n";
		$exceptionReference .= "  Exception code      #" . $exception->getCode() . "\n";
		$exceptionReference .= "  File                " . $exception->getFile() . ($exception->getLine() ? ' line ' . $exception->getLine() : '') . "\n";
		$exceptionReference .= ($exception instanceof \TYPO3\FLOW3\Exception ? "  Exception reference #" . $exception->getReferenceCode() . "\n" : '');
		foreach (explode(chr(10), wordwrap($exception->getMessage(), 73)) as $messageLine) {
			 $exceptionMessage .= "  $messageLine\n";
		}

		$response->setContent(sprintf("<b>Uncaught Exception</b>\n%s%s\n", $exceptionMessage, $exceptionReference));
		$response->send();
		exit(1);
	}
}

?>