<?php

namespace QuizBot\Exception;

class QuizBotExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers QuizBot\Exception\QuizBotException::__construct
     * @expectedException QuizBot\Exception\QuizBotException
     */
    public function testConstruct()
    {
        throw new \QuizBot\Exception\QuizBotException('bla bla bla');
    }
}
