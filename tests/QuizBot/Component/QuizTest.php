<?php

namespace QuizBot\Component;

use QuizBot\Component\Quiz;

class QuizTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->quiz = new Quiz();

        $this->category = array(
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

        $this->quiz->addCategory($this->category);
        $this->quiz->prepare();
    }

    /**
     * @covers QuizBot\Component\Quiz::setTwig
     * @covers QuizBot\Component\Quiz::getTwig
     */
    public function testGetSetTwig()
    {
        $this->quiz->setTwig(new \Twig_Environment(new \Twig_Loader_String()));
        $this->assertInstanceOf('Twig_Environment', $this->quiz->getTwig());
    }

    /**
     * @covers QuizBot\Component\Quiz::addCategory
     * @covers QuizBot\Component\Quiz::getRepository
     */
    public function testAddCategoryGetRepository()
    {
        $this->assertEquals($this->quiz->getRepository(), $this->category);
    }

    /**
     * @covers QuizBot\Component\Quiz::prepare
     * @covers QuizBot\Component\Quiz::getQuestions
     * @covers QuizBot\Component\Quiz::getRandomQuestionFromRepository
     */
    public function testPrepare()
    {
        $this->assertEquals(3, count($this->quiz->getQuestions()));
    }

    /**
     * @covers QuizBot\Component\Quiz::prepare
     * @expectedException QuizBot\Exception\QuizBotException
     */
    public function testPrepareException()
    {
        $quiz = new Quiz();
        $quiz->prepare();
    }

    /**
     * @covers QuizBot\Component\Quiz::getQuestion
     */
    public function testGetQuestion()
    {
        $this->assertInstanceOf('QuizBot\Component\Question', $this->quiz->getQuestion('question-3'));
        $this->assertTrue(is_null($this->quiz->getQuestion('invalid-key')));
    }

    public function testGetQuestionBad()
    {
        $quiz = new Quiz();
        $q = $quiz->getQuestion('badkey');
    }

    /**
     * @covers QuizBot\Component\Quiz::removeQuestion
     */
    public function testRemoveQuestion()
    {
        $this->quiz->removeQuestion('question-1');
        $this->assertEquals(null, $this->quiz->getQuestion('question-1'));
    }

    /**
     * @covers QuizBot\Component\Quiz::getRandomQuestion
     */
    public function testGetRandomQuestion()
    {
        // Grab a random question.
        $question = $this->quiz->getRandomQuestion();
        $this->assertInstanceOf('QuizBot\Component\Question', $question);

        // Set it to correct.
        $question->setCorrect(true);
        $key = $question->getKey();
        unset($question);

        // Grab another correct question (only $question will be available).
        // We want to ensure we can fetch questions based on a correct/incorrect filter.
        $question = $this->quiz->getRandomQuestion(true);
        $this->assertInstanceOf('QuizBot\Component\Question', $question);
        $this->assertEquals($key, $question->getKey());

        $quiz = new Quiz();
        $this->assertTrue(!$quiz->getRandomQuestion());
    }

    /**
     * @covers QuizBot\Component\Quiz::tally
     * @covers QuizBot\Component\Quiz::getResults
     */
    public function testTallyAndResults()
    {
        $this->quiz->tally('correct');
        $this->quiz->tally('incorrect');
        $this->quiz->tally('incorrect');

        $results = $this->quiz->getResults();
        $this->assertEquals(1, $results['correct']);
        $this->assertEquals(2, $results['incorrect']);
        $this->assertEquals('33.33%', $results['score']);
    }

    /**
     * @covers QuizBot\Component\Quiz::countQuestions
     */
    public function testCountQuestions()
    {
        $this->assertEquals(3, $this->quiz->countQuestions());
    }

    /**
     * @covers QuizBot\Component\Quiz::hasQuestions
     */
    public function testHasQuestions()
    {
        $this->assertTrue($this->quiz->hasQuestions());
    }
}
