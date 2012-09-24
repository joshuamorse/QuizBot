<?php

namespace QuizBot\Component;

use QuizBot\Component\Quiz;

class Question
{
    /** Represents possible answers for this question. */
    protected $answers;

    /** Represents whether this question was answered correctly. */
    protected $correct;

    /** Represents a data entry for this question. */
    protected $entry;

    protected $twig;

    /** Represents variables for this question. */
    protected $variables;

    /** Represents variants for this question. */
    protected $variants;

    /** Represents this question. */
    protected $question;

    /** Represents the quiz to which this question belongs. */
    protected $quiz;

    /**
     * __construct
     *
     * @param array $entry
     * @return void
     */
    public function __construct($key, array $entry, array $config = null)
    {
        $this->setData($entry);
        $this->setKey($key);
    }

    /**
     * Returns a Twig_Environment object.
     *
     * @access protected
     * @return Twig_Environment $this->twig
     */
    public function getTwig()
    {
        $twig = $this->quiz->getTwig();

        return $twig;
    }

    /**
     * Prepares a question to be asked:
     *   - Selects a random variant, if applicable.
     *   - Parses variables where applicable.
     *
     * @return void
     */
    public function prepare()
    {
        if ($this->getVariables()) {
            // Parse the defined variables with twig first.
            foreach ($this->entry['variables'] as $key => $value) {
                $this->variables[$key] = $this->getTwig()->render($value);
            }

            // Then parse other question info.
            $this->parse($this->question);
            $this->parse($this->answers);
            $this->parse($this->variants);
        }
    }

    /**
     * A proxy method to ``prepare()``.
     *
     * @access public
     * @return void
     */
    public function andPrepare()
    {
        $this->prepare();

        return $this;
    }

    /**
     * Sets data related to the question.
     *
     * @param array $entry
     * @return void
     */
    public function setData(array $entry)
    {
        $this->entry = $entry;

        if (isset($entry['answers'])) {
            $this->setAnswers($entry['answers']);
        }

        $this->setQuestion($this->getQuestionByEntry($entry));

        if (isset($entry['variables'])) {
            $this->setVariables($entry['variables']);
        }
    }

    /**
     * Attempts to intelligently return a question based on a number of factors:
     *   - Has a single question been specified?
     *   - Have variants been specified?
     *   - Is this a true/false question?
     *
     * @param array $entry
     * @access protected
     * @return string $question
     */
    protected function getQuestionByEntry(array $entry)
    {
        /**
         * If the entry has a question defined, set it. Otherwise, we'll set variants
         * and select a random one for the question.
         */
        if (isset($entry['question'])) {
            // Account for instances where a single question is set in an array.
            if (is_array($entry['question']) && count($entry['question']) === 1) {
                $question = $entry['question'][0];
            } else {
                $question = $entry['question'];
            }
        } else if (isset($entry['variants'])) { 
            $question = $entry['variants'][array_rand($entry['variants'])];
            $this->setVariants($entry['variants']);
        }

        // Append "(true/false)" true false questions.
        if ($this->isTrueFalse()) {
            $question .= ' (true/false)';
        }

        return $question;
    }

    /**
     * Determines if this is a true-false question.
     *
     * @access protected
     * @return boolean $isTrueFalse
     */
    protected function isTrueFalse()
    {
        $isTrueFalse = preg_match('/(true|false)+/', $this->getReadableAnswers());

        return $isTrueFalse;
    }

    /**
     * Parses a subject by rendering it via twig.
     * This includes the parsing of any declared variables.
     * If an array is passed, items will be parsed 1-level deep.
     *
     * @param mixed $subject
     * @access protected
     * @return void
     */
    protected function parse(&$subject)
    {
        if (is_array($subject)) {
            foreach ($subject as $key => $item) {
                $subject[$key] = $this->getTwig()->render($item, $this->variables);
            }
        } else {
            $subject = $this->getTwig()->render($subject, $this->variables);
        }
    }

	/**
	 * Determines if supplied input is a valid answer.
	 *
	 * @param mixed $answer
	 * @access public
	 * @return boolean $validate
	 */
	public function validate($input)
	{
		$input = trim(strtolower($input));
        $validate = in_array($input, $this->getAnswers());

		return $validate;
	}

    /**
     * Removes this question from its quiz and resets the quiz property.
     *
     * @access public
     * @return void
     */
    public function removeFromQuiz()
    {
        $this->quiz->removeQuestion($this->key);
        $this->quiz = null;
    }

    /**
     * Returns this question's key.
     *
     * @access public
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * setAnswers
     *
     * @return void
     */
    protected function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * setVariants
     *
     * @return void
     */
    protected function setVariants($variants)
    {
        $this->variants = $variants;
    }

    /**
     * Assigns a question based on a random variant.
     *
     * @return void
     */
    protected function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Assigns variables.
     * Will assign a random value if multiple vales are supplied.
     *
     * @access protected
     */
    protected function setVariables($variables)
    {
        $this->variables = $variables;
    }

    public function getCorrect()
    {
        return $this->correct;
    }

    public function setCorrect($correct)
    {
        $this->correct = $correct;
    }

    public function setEntry(array $entry)
    {
        $this->entry = $entry;
    }

    public function getAnswers()
    {
        return $this->answers;
    }

    public function getReadableAnswers()
    {
        $answers = implode(', ', $this->getAnswers());

        return $answers;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function getQuiz()
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }
}
