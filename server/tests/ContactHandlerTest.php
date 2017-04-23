<?php
class ContactHandler extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $mailer;

    protected $digest;

    protected $request;

    protected function setUp()
    {
        $this->mailer = \Mockery::mock('\JHM\MailerInterface');
        $this->digest = \Mockery::mock('\JHM\MailDigestInterface');
        $this->request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $this->obj = new \JHM\ContactHandler($this->mailer, $this->digest);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testProcess()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('setRelpyTo')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setFrom')->once();
        $this->mailer->shouldReceive('setBody')->times(6);
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->digest->shouldReceive('writeMessage')->once();
        $result = $this->obj->process($request);
        $this->assertEquals(200, $this->obj->status());
    }

    public function testFailProcess()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('setRelpyTo')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setFrom')->once();
        $this->mailer->shouldReceive('setBody')->times(6);
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->digest->shouldReceive('writeMessage')->once();
        $result = $this->obj->process($request);
        $this->assertEquals(500, $this->obj->status());
    }

    public function testBadRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
            )
        );
        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

    public function testInvalidEmail()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );

        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

    public function testEmptyProp()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => '',
            )
        );

        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

}
