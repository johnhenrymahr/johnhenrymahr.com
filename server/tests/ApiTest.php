<?php

use Symfony\Component\HttpFoundation\Request;

class ApiTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $loggerMock;

    protected $responseMock;

    protected function setUp()
    {
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->responseMock = \Mockery::mock('\Symfony\Component\HttpFoundation\JsonResponse');
        $this->responseMock->shouldReceive('prepare');
        $this->obj = new \JHM\Api($this->loggerMock);
    }

    protected function tearDown()
    {
        $this->loggerMock = null;
        $this->responseMock = null;
        $this->obj = null;
        \Mockery::close();
    }

    public function testSuccessfullHandlerHandoff()
    {
        $expected = [
            'statusCode' => 200,
            'foo' => 'bar',
        ];
        $request = Request::create('/api', 'POST', array('component' => 'test'));
        $handler = \Mockery::mock('\JHM\ApiHandlerInterface');
        $handler->shouldReceive('process')->with($request)->once()->andReturn(true);
        $handler->shouldReceive('status')->andReturn(200);
        $handler->shouldReceive('body')->andReturn(['foo' => 'bar']);
        $this->responseMock->shouldReceive('setStatusCode')->with(200)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->obj->setResponse($this->responseMock);
        $this->obj->handler('test', $handler);
        $this->obj->init($request);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
    }

    public function testCallback()
    {
        $called = false;
        $expected = [
            'statusCode' => 200,
            'foo' => 'bar',
        ];
        $request = Request::create('/', 'GET');
        $handler = \Mockery::mock('\JHM\ApiHandlerInterface');
        $handler->shouldReceive('process')->with($request)->once()->andReturn(true);
        $handler->shouldReceive('status')->andReturn(200);
        $handler->shouldReceive('body')->andReturn(['foo' => 'bar']);
        $this->responseMock->shouldReceive('setStatusCode')->with(200)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->obj->setResponse($this->responseMock);
        $handler->callbacks[] = function () use (&$called) {
            $called = true;
        };
        $this->obj->defaultHandler($handler);
        $this->obj->init($request);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
        $this->assertTrue($called);
    }

    public function testHandlerResponse()
    {
        $called = false;
        $expected = [
            'statusCode' => 200,
            'foo' => 'bar',
        ];
        $request = Request::create('/', 'GET');
        $handler = \Mockery::mock('\JHM\ApiHandlerInterface');
        $handler->shouldReceive('process')->with($request)->once()->andReturn(true);
        $handler->shouldReceive('status')->andReturn(200);
        $handler->shouldReceive('body')->andReturn(['foo' => 'bar']);
        $this->responseMock->shouldReceive('setStatusCode')->with(200)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $handler->response = $this->responseMock;
        $this->obj->defaultHandler($handler);
        $this->obj->init($request);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
    }

    public function testDefaultHandler()
    {
        $expected = [
            'statusCode' => 200,
            'foo' => 'bar',
        ];
        $request = Request::create('/', 'GET');
        $handler = \Mockery::mock('\JHM\ApiHandlerInterface');
        $handler->shouldReceive('process')->with($request)->once()->andReturn(true);
        $handler->shouldReceive('status')->andReturn(200);
        $handler->shouldReceive('body')->andReturn(['foo' => 'bar']);
        $this->responseMock->shouldReceive('setStatusCode')->with(200)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->obj->setResponse($this->responseMock);
        $this->obj->defaultHandler($handler);
        $this->obj->init($request);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
    }

    public function testHandlerNotFound()
    {
        $expected = [
            'statusCode' => 404,
        ];
        $request = Request::create('/api', 'POST', array('component' => 'test'));
        $this->responseMock->shouldReceive('setStatusCode')->with(404)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->obj->setResponse($this->responseMock);
        $this->obj->init($request);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
    }

    public function testSetResponseFail()
    {
        $this->expectException(\TypeError::class);
        $this->obj->setResponse(['foo' => 'bar']);
    }

    public function testNoComponent()
    {
        $expected = [
            'statusCode' => 400,
        ];
        $request = Request::create('/api', 'POST', array());
        $this->responseMock->shouldReceive('setStatusCode')->with(400)->once();
        $this->responseMock->shouldReceive('setData')->with($expected)->once();
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->obj->setResponse($this->responseMock);
        $this->obj->init($request);
        $this->responseMock->shouldReceive('send')->andReturn($this->responseMock);
        $this->assertEquals($this->obj->respond(), $this->responseMock);
    }
}
