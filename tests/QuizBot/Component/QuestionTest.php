<?php

namespace QuizBot\Component;

use QuizBot\Component\Question;

class QuestionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entry = array(
            'question-1' => array(
                'variables' => array(
                    'randomLetter' => "{{ random(['a', 'b', 'c']) }}",
                    'randomNumber' => '{{ random(1000) }}',
                ),
                'variants' => array(
                    'Which of the following is a letter: {{ randomLetter }} or {{ randomNumber }}?',
                    'Identify the letter: {{ randomNumber }}, {{ randomLetter }}.',
                ),
                'answers' => array('{{ randomLetter }}'),
            ),
            'question-2' => array(
                'question' => '15 is greater than 2.',
                'answers' => array('true', 'yes', '1'),
            ),
            'question-3' => array(
                'question' => array(
                    'something here?',
                ),
                'answers' => array('whatever'),
            ),

        );

        $this->questionOne = new Question('question-1', $this->entry['question-1'], null);
        $this->questionTwo = new Question('question-2', $this->entry['question-2'], null);
        $this->questionThree = new Question('question-3', $this->entry['question-3'], null);

        $quiz = $this->getMockBuilder('QuizBot\Component\Quiz', array('getTwig'))->getMock();
        $quiz->expects($this->any())
            ->method('getTwig')
            ->will($this->returnValue(new \Twig_Environment(new \Twig_Loader_String())));
        $quiz->expects($this->any())
            ->method('removeQuestion')
            ->with('question-2')
            ->will($this->returnValue(null));

        $this->assertInstanceOf('Twig_Environment', $quiz->getTwig());

        $this->questionOne->setQuiz($quiz);
        $this->questionTwo->setQuiz($quiz);
        $this->questionThree->setQuiz($quiz);
    }

    /**
     * @covers QuizBot\Component\Question::__construct
     * @covers QuizBot\Component\Question::setData
     * @covers QuizBot\Component\Question::setKey
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('QuizBot\Component\Question', $this->questionOne);
    }

    /**
     * @covers QuizBot\Component\Question::getKey
     */
    public function testGetKey()
    {
        $this->assertEquals($this->questionOne->getKey(), 'question-1');
    }

    public function testGetSetEntry()
    {
        $this->assertEquals(is_array($this->questionOne->getEntry()), true);

        $array = array('foo', 'bar');
        $this->questionOne->setEntry($array);
        $this->assertEquals($this->questionOne->getEntry(), $array);

        $this->questionOne->setEntry($this->entry['question-1']);
    }

    /**
     * @covers QuizBot\Component\Question::getCorrect
     * @covers QuizBot\Component\Question::setCorrect
     */
    public function testGetSetCorrect()
    {
        $this->questionOne->setCorrect(true);
        $this->assertTrue($this->questionOne->getCorrect());
        $this->questionOne->setCorrect(false);
        $this->assertTrue(!$this->questionOne->getCorrect());
        $this->questionOne->setCorrect(null);
    }

    /**
     * @covers QuizBot\Component\Question::getQuiz
     * @covers QuizBot\Component\Question::getTwig
     */
    public function testGetSetTwig()
    {
        $this->assertInstanceOf('QuizBot\Component\Quiz', $this->questionOne->getQuiz());
        $this->assertInstanceOf('Twig_Environment', $this->questionOne->getQuiz()->getTwig());
        $this->assertInstanceOf('Twig_Environment', $this->questionOne->getTwig());
    }

    public function testGetVariables()
    {
        $variables = $this->questionOne->getVariables();
        $this->assertTrue(is_array($variables));
        $this->assertTrue(!is_null($variables['randomLetter']));
        $this->assertTrue($variables['randomNumber'] < 1001);
        $this->assertTrue($variables['randomNumber'] >= 0);
    }

    public function testGetVariants()
    {
        $variants = $this->questionOne->getVariants();
        $this->assertTrue(is_array($variants));
        $this->assertEquals(count($variants), 2);
    }

    /**
     * @covers QuizBot\Component\Question::getAnswers
     */
    public function testGetAnswers()
    {
        $this->assertEquals(1, count($this->questionOne->getAnswers()));
        $this->assertEquals(3, count($this->questionTwo->getAnswers()));
    }

    public function testGetQuestion()
    {
        $this->assertTrue(in_array($this->questionOne->getQuestion(), $this->entry['question-1']['variants']));
        $this->assertEquals(1, preg_match('/.*true.{1}false.{1}/', $this->questionTwo->getQuestion()));
        $this->assertTrue(in_array($this->questionThree->getQuestion(), $this->entry['question-3']['question']));
    }

    /**
     * @covers QuizBot\Component\Question::prepare
     * @covers QuizBot\Component\Question::andPrepare
     * @covers QuizBot\Component\Question::getQuestion
     * @covers QuizBot\Component\Question::parse
     * @covers QuizBot\Component\Question::getAnswers
     * @covers QuizBot\Component\Question::validate
     */
    public function testAllTheThings()
    {
        $this->assertTrue(in_array($this->questionOne->getQuestion(), $this->entry['question-1']['variants']));
        $this->questionOne->andPrepare();
        $this->assertTrue(!in_array($this->questionOne->getQuestion(), $this->entry['question-1']['variants']));

        $answers = $this->questionOne->getAnswers();
        $this->assertEquals(count($answers), 1);

        $valid = false;
        $answers = array('a', 'b', 'c');

        foreach ($answers as $answer) {
            if ($this->questionOne->validate($answer)) {
                $this->assertTrue($this->questionOne->validate($answer));
                break;
            }
        }

        unset($valid, $answers);

        $valid = true;
        $answers = array('true', 'yes', '1');

        foreach ($answers as $answer) {
            if (!$this->questionTwo->validate($answer)) {
                $valid = false;
                break;
            }
        }

        $this->assertTrue($valid);
    }

    /**
     * @covers QuizBot\Component\Question::removeFromQuiz
     */
    public function testRemoveFromQuiz()
    {
        $this->assertInstanceOf('QuizBot\Component\Quiz', $this->questionTwo->getQuiz());
        $this->questionTwo->removeFromQuiz();
        $this->assertTrue(is_null($this->questionTwo->getQuiz()));
    }
}
