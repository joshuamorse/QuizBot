<?php

namespace QuizBot\Component;

use QuizBot\Component\Question;
use QuizBot\Exception\QuizBotException;

class Quiz
{
    /** Represents a data entry for this question. */
    protected $entry;

    /** Represents the questions to be asked as part of this quiz. */
    protected $questions;

    /** Represents the collection of question entries from supplied categories. */
    protected $repository;

    /** Represents an instance of Twig_Environment. */
    protected $twig;
    
    /** Represents a counter of correct/incorrect answers. */
    protected $counter = array('correct' => null, 'incorrect' => null, 'total' => null);

    /**
     * Prepares a quiz for taking.
     * Populates the quiz with $amount random questions from $this->repository.
     * 
     * @param int $amount 
     * @return void
     */
    public function prepare($amount = null)
    {
        if ($amount === null) {
            $amount = count($this->getRepository());
        }

        if (!$amount) {
            throw new QuizBotException('No questions were found to prepare. Are you sure valid categories were added to the quiz?');
        }

        // Init the questions array.
        $this->questions = array();

        // Fill the questions array until we reach our $amount limit.
        while (true) {
            $question = $this->getRandomQuestionFromRepository();            
            $question->setQuiz($this);

            $this->questions[$question->getKey()] = $question;

            if (count($this->questions) == $amount) {
                break;
            }
        }
    }

    /**
     * Returns a random question object from the $repository array.
     * 
     * @return Question $question
     */
    protected function getRandomQuestionFromRepository()
    {
        $repository = $this->getRepository();
        $key = array_rand($repository);
        $entry = $repository[$key];

        // Instantiate a new Question.
        $question = new Question($key, $entry);

        return $question;
    }

    /**
     * Returns a random question from the $questions array.
     * 
     * @return Question $question
     */
    public function getRandomQuestion($correct = false)
    {
        $questions = $this->getQuestions();

        // Bail out if we don't have any questions.
        if (!count($questions)) {
            return false;
        }

        while (true) {
            $question = $questions[array_rand($questions)];

            // Only grab a correct question, if specified.
            if ($correct && $question->getCorrect()) {
                break;
            // Only grab a incorrect question, if specified.
            } else if (!$correct && !$question->getCorrect()) {
                break;
            }
        }
        
        return $question;
    }

    /**
     * Merges a category entry collection with the $repository array.
     * 
     * @param mixed $category 
     * @return void
     */
    public function addCategory(array $category)
    {
        $this->repository = array_merge($category, $this->repository ?: array());
    }

    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns a question via a $key. 
     * 
     * @param mixed $key 
     * @return Question $question
     */
    public function getQuestion($key)
    {
        if (!isset($this->questions[$key])) {
            return null;
        }

        $question = $this->questions[$key];

        return $question;
    }

    /**
     * Returns this quiz's questions.
     * 
     * @access public
     * @return array
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Removes a question.
     * 
     * @param mixed $key 
     * @return void
     */
    public function removeQuestion($key)
    {
        if (isset($this->questions[$key])) {
            unset($this->questions[$key]);
        }
    }

    /**
     * Determines if this quiz has any questions.
     * 
     * @return boolean $hasQuestions
     */
    public function hasQuestions()
    {
        $hasQuestions = ($this->countQuestions() > 0);

        return $hasQuestions;
    }

    /**
     * Counts the amount of questions associated with this quiz.
     * 
     * @return integer $count
     */
    public function countQuestions()
    {
        $count = count($this->questions);

        return $count;
    }

    /**
     * Sets a Twig environment.
     * 
     * @param \Twig_Environment $twig 
     * @return void
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * Increments the counter given a $key.
     * 
     * @param mixed $key 
     * @return void
     */
    public function tally($key)
    {
        if (array_key_exists($key, $this->counter)) {
            $this->counter[$key]++;
        }
    }

    /**
     * Returns an array of quiz results.
     * 
     * @return array $results
     */
    public function getResults()
    {
        $results = $this->counter;
        $results['total'] = ($this->counter['correct'] + $this->counter['incorrect']);
        $results['score'] = number_format((($results['correct'] / $results['total']) * 100), 2) . '%';

        return $results;
    }
}
