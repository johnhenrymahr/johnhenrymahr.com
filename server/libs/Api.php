<?php
namespace JHM;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Api implements ApiInterface
{

    protected $logger;

    protected $request;

    protected $response;

    protected $responseBody = array();

    protected $handlers = array();

    protected $callbacks = array();

    protected $handler; // fall back to this handler if no component set

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function init($request = '')
    {
        if (is_object($request) && $request instanceof Request) {
            $this->_init($request);
        } else {
            $this->_init(Request::createFromGlobals());
        }
    }

    public function setResponse(Response $response)
    {
        $this->logger->log('INFO', 'set response', ['request object' => $response]);
        $this->response = $response;
    }

    public function handler($id, ApiHandlerInterface $handler)
    {
        if (is_string($id)) {
            $this->handlers[$id] = $handler;
        }
    }

    public function defaultHandler(ApiHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function respond()
    {
        if (!is_object($this->response)) {
            throw new JhmException('Api not ready. No response object. Call setResponse.');
        }
        if (!empty($this->responseBody) && method_exists($this->response, 'setData')) {
            $this->response->setData($this->responseBody);
        }

        $this->__callbacks();

        if (method_exists($this->response, 'prepare')) {
            $this->response->prepare($this->request);
        }

        return $this->response->send();
    }

    protected function _init($request)
    {
        $this->request = $request;

        $status = $this->_processHandlers();
        if (!is_int($status)) {
            Response::HTTP_BAD_REQUEST;
        }
        $this->status($status);
    }

    protected function mergeBody(array $body)
    {
        return array_merge($this->responseBody, $body);
    }

    protected function status($statusCode)
    {
        if (!is_object($this->response)) {
            throw new JhmException('Api not ready. No response object. Call setResponse.');
        }
        $this->response->setStatusCode($statusCode);
        $this->responseBody['statusCode'] = $statusCode;
    }

    private function __callbacks()
    {
        if (!empty($this->callbacks)) {
            foreach ($this->callbacks as $cb) {
                if (is_callable($cb)) {
                    $cb();
                }
            }
        }
    }

    protected function _getComponentKey()
    {
        if ($this->request->request->has('component')) {
            $requestKey = $this->request->request->filter('component', '', FILTER_SANITIZE_STRING);
            $this->logger->log('INFO', 'post request key', ['key' => $requestKey]);
            return $requestKey;
        }
        if ($this->request->query->has('component')) {
            $requestKey = $this->request->query->filter('component', '', FILTER_SANITIZE_STRING);
            $this->logger->log('INFO', 'query param request key', ['key' => $requestKey]);
            return $requestKey;
        }
        $this->logger->log('INFO', 'no key found in request.', ['post' => $_POST, 'get' => $_GET]);
        return false;
    }

    protected function _processHandler(ApiHandlerInterface $component)
    {
        if ($component->process($this->request)) {
            $this->responseBody = $this->mergeBody($component->body());
            if (isset($component->callbacks) && is_array($component->callbacks) && !empty($component->callbacks)) {
                $this->callbacks = array_merge($this->callbacks, $component->callbacks);
            }
            if (isset($component->response) && is_object($component->response)) {
                $this->setResponse($component->response);
            }
            return $component->status();
        }
    }

    protected function _processHandlers()
    {
        $key = $this->_getComponentKey();
        $this->logger->log('INFO', 'Parsing handlers', ['handlers' => $this->handlers, 'key' => $key]);
        if ($key) {
            if (array_key_exists($key, $this->handlers)) {
                $component = $this->handlers[$key];
                $this->logger->log('INFO', 'Processing handlers ' . $key);
                return $this->_processHandler($component);
            } else {
                return Response::HTTP_NOT_FOUND;
            }
        } elseif (is_object($this->handler) && $this->handler instanceof ApiHandlerInterface) {
            return $this->_processHandler($this->handler);
        }
        return Response::HTTP_BAD_REQUEST;
    }

}
