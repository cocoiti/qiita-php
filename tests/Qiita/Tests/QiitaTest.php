<?php

namespace Qiita\Tests;

use Qiita\Qiita;


class QiitaTest extends \PHPUnit_Framework_TestCase
{
    protected $qiita;

    public function setUp()
    {
        $this->qiita = new Qiita('test');
        $this->qiita->setBaseUrl('http://localhost');
    }

    public function testQiitaException()
    {
        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        // Always be failed
        $response->expects($this->any())
            ->method('isSuccessful')
            ->will($this->returnValue(false));

        // status code is "401"
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(401));

        // error data
        $response->expects($this->any())
            ->method('json')
            ->will($this->onConsecutiveCalls([
                [
                    'type' => 'not_found',
                    'message' => 'Not found',
                ],
             ]));

        $event = $this->getMock('Guzzle\\Common\\Event', null, [
            [
                'response' => $response,
            ],
        ]);

        try {
            $this->qiita->onRequestError($event);
        } catch (\Qiita\Exception\QiitaException $e) {
            $this->assertInstanceOf('Guzzle\\Http\\Message\\Response', $e->getResponse());
            $this->assertEquals(401, $e->getCode());
        }
    }
}
