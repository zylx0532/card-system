<?php
 namespace Predis\Connection; use Predis\Command\CommandInterface; use Predis\CommunicationException; use Predis\Protocol\ProtocolException; abstract class AbstractConnection implements NodeConnectionInterface { private $resource; private $cachedId; protected $parameters; protected $initCommands = array(); public function __construct(ParametersInterface $parameters) { $this->parameters = $this->assertParameters($parameters); } public function __destruct() { $this->disconnect(); } abstract protected function assertParameters(ParametersInterface $parameters); abstract protected function createResource(); public function isConnected() { return isset($this->resource); } public function connect() { if (!$this->isConnected()) { $this->resource = $this->createResource(); return true; } return false; } public function disconnect() { unset($this->resource); } public function addConnectCommand(CommandInterface $command) { $this->initCommands[] = $command; } public function executeCommand(CommandInterface $command) { $this->writeRequest($command); return $this->readResponse($command); } public function readResponse(CommandInterface $command) { return $this->read(); } private function createExceptionMessage($message) { $parameters = $this->parameters; if ($parameters->scheme === 'unix') { return "$message [$parameters->scheme:$parameters->path]"; } if (filter_var($parameters->host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) { return "$message [$parameters->scheme://[$parameters->host]:$parameters->port]"; } return "$message [$parameters->scheme://$parameters->host:$parameters->port]"; } protected function onConnectionError($message, $code = null) { CommunicationException::handle( new ConnectionException($this, static::createExceptionMessage($message), $code) ); } protected function onProtocolError($message) { CommunicationException::handle( new ProtocolException($this, static::createExceptionMessage($message)) ); } public function getResource() { if (isset($this->resource)) { return $this->resource; } $this->connect(); return $this->resource; } public function getParameters() { return $this->parameters; } protected function getIdentifier() { if ($this->parameters->scheme === 'unix') { return $this->parameters->path; } return "{$this->parameters->host}:{$this->parameters->port}"; } public function __toString() { if (!isset($this->cachedId)) { $this->cachedId = $this->getIdentifier(); } return $this->cachedId; } public function __sleep() { return array('parameters', 'initCommands'); } } 