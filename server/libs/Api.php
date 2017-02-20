<?php
namespace JHM;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Api implements ApiInterface
{

    protected $log;

    protected $request;

    protected $response;

    protected $responseBody = array();

    protected $handlers = array();

    public function __construct(JsonResponse $response, LoggerInterface $logger)
    {
        $this->response = $response;
        $this->log = $logger;
    }

    public function init($request)
    {
        if (is_object($request) && $request instanceof Request) {
            $this->_init($request);
        } else {
            $this->_init(Request::createFromGlobals());
        }
    }

    public function handler($id, ApiHandlerInterface $handler)
    {
        if (is_string($id)) {
            $this->handlers[$id] = $handler;
        }
    }

    public function respond()
    {
        $this->response->setData($this->responseBody);
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
        $this->response->setStatusCode($statusCode);
        $this->responseBody['statusCode'] = $statusCode;
    }

    protected function _processHandlers()
    {
        if ($this->request->request->has('component')) {
            $key = $this->request->request->filter('component', '', FILTER_SANITIZE_STRING);
            if (array_key_exists($key, $this->handlers)) {
                $component = $this->handlers[$key];
                if ($component->process($this->request)) {
                    $this->responseBody = $this->mergeBody($component->body());
                    return $component->status();
                }
            } else {
                return Response::HTTP_NOT_FOUND;
            }
        }
        return Response::HTTP_BAD_REQUEST;
    }

}
