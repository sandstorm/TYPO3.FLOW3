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
 * A special request handler which handles "slave" command requests as used by
 * the interactive shell.
 *
 * @FLOW3\Proxy(false)
 * @FLOW3\Scope("singleton")
 */
class SlaveRequestHandler implements \TYPO3\FLOW3\Core\RequestHandlerInterface {

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
		return (PHP_SAPI === 'cli' && isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === '--start-slave');
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler.
	 */
	public function getPriority() {
		return 200;
	}

	/**
	 * Creates an event loop which takes orders from the parent process and executes
	 * them in runtime mode.
	 *
	 * @return void
	 */
	public function handleRequest() {
		$sequence = $this->bootstrap->buildRuntimeSequence();
		$sequence->invoke($this->bootstrap);

		$objectManager = $this->bootstrap->getObjectManager();
		$systemLogger = $objectManager->get('TYPO3\FLOW3\Log\SystemLoggerInterface');

		$systemLogger->log('Running sub process loop.', LOG_DEBUG);
		echo "\nREADY\n";

		try {
			while (TRUE) {
				$commandLine = trim(fgets(STDIN));
				$systemLogger->log(sprintf('Received command "%s".', $commandLine), LOG_INFO);
				if ($commandLine === "QUIT\n") {
					break;
				}
				$this->request = $objectManager->get('TYPO3\FLOW3\MVC\CLI\RequestBuilder')->build($commandLine);
				$response = new \TYPO3\FLOW3\MVC\CLI\Response();
				if ($this->bootstrap->isCompiletimeCommand($this->request->getCommand()->getCommandIdentifier())) {
					echo "This command must be executed during compiletime.\n";
				} else {
					$objectManager->get('TYPO3\FLOW3\MVC\Dispatcher')->dispatch($this->request, $response);
					$response->send();

					$this->emitDispatchedCommandLineSlaveRequest();
				}
				echo "\nREADY\n";
			}

			$systemLogger->log('Exiting sub process loop.', LOG_DEBUG);
			$this->bootstrap->shutdown('Runtime');
			exit($response->getExitCode());
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
	 * Emits a signal that a CLI slave request was dispatched.
	 *
	 * @return void
	 * @FLOW3\Signal
	 */
	protected function emitDispatchedCommandLineSlaveRequest() {
		$this->bootstrap->getSignalSlotDispatcher()->dispatch(__CLASS__, 'dispatchedCommandLineSlaveRequest', array());
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