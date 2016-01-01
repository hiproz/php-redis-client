<?php

namespace RedisClient\Command;

use RedisClient\Command\Response\ResponseParser;

class Command implements CommandInterface {

    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var \Closure
     */
    protected $responseParser;

    /**
     * @param string $command
     * @param mixed $parameters
     * @param \Closure|int|string|null $responseParser
     */
    public function __construct($command, $parameters = null, $responseParser = null) {
        $this->command = $command;
        if (isset($parameters)) {
            if (is_array($parameters)) {
                $this->addParameters($parameters);
            } else {
                $this->addParameter($parameters);
            }
        }
        if ($responseParser) {
            $this->responseParser = $responseParser;
        }
    }

    /**
     * @param mixed $parameter
     */
    protected function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }

    /**
     * @param array $parameters
     */
    protected function addParameters(array $parameters) {
        foreach ($parameters as $Parameter) {
            $this->addParameter($Parameter);
        }
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->command;
    }

    /**
     * @return string[]
     */
    public function getStructure() {
        $structure = preg_split('/\s+/', $this->command);

        foreach ($this->parameters as $parameter) {
            if (is_array($parameter)) {
                $structure = array_merge($structure, $parameter);
            } else {
                $structure[] = $parameter;
            }
        }

        return $structure;
    }

    /**
     * @param mixed $response
     * @return mixed
     */
    public function parseResponse($response) {
        if (is_int($this->responseParser)) {
            return ResponseParser::parse($this->responseParser, $response);
        } elseif ($this->responseParser instanceof \Closure) {
            $Parser = $this->responseParser;
            return $Parser($response);
        } elseif (is_string($this->responseParser)) {
            return call_user_func($this->responseParser, $response);
        }
        return $response;
    }

}
