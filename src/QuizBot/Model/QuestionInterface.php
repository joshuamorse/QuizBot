<?php

namespace QuizBot\Model;

interface QuestionInterface
{
	/**
	 * Prepares question/answer data, if needed.
	 */
	function prepare();

	/**
	 * Sets incoming data from data source.
	 */
	function setData(array $data);

	/**
	 * Validates answer/s.
	 */
	function validate($input);

    /**
     * Returns valid answers.
     */
    function getAnswers();

    /**
     * Returns valid questions.
     */
    function getQuestion();
}
