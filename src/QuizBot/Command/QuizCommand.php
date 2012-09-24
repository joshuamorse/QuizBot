<?php

namespace QuizBot\Command;

use QuizBot\Component\Question;
use QuizBot\Component\Quiz;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuizCommand extends Command
{
    protected $quiz;
    protected $yaml;

    protected function configure()
    {
        $this
            ->setName('jmflava:quiz')
            ->setDescription('Start a quiz.')
            ->addOption('categories', null, InputOption::VALUE_OPTIONAL, 'A comma-separated list of which categories to load.')
            ->addOption('questions', null, InputOption::VALUE_OPTIONAL, 'The amount of questions to ask.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init();

        $quiz = new Quiz();
        $quiz->setTwig(new \Twig_Environment(new \Twig_Loader_String()));
        $this->loadCategoriesForQuiz($quiz, $input->getOption('categories'));
        $quiz->prepare();

        while (true) {
            $input->getOption('questions');
            if ($quiz->hasQuestions()) {
                $question = $quiz->getRandomQuestion()->andPrepare();

                $ask = '<question>' . $question->getQuestion() . '</question>';
                $answer = $this->dialog->ask($output, $ask);

                if ($question->validate($answer)) {
                    $quiz->tally('correct');
                    $output->writeln('<info>Correct!</info>');
                    $this->outputResults($quiz, $output);
                    $question->setCorrect(true);
                    $question->removeFromQuiz();
                } else {
                    $quiz->tally('incorrect');
                    $output->writeln('<error>Incorrect!</error>');
                    $output->writeln('<comment>Acceptable answers: ' . $question->getReadableAnswers() . '.</comment>');
                    $this->outputResults($quiz, $output);
                    $question->setCorrect(false);
                }

                $output->writeln('');
            } else {
                break;
            }
        }

        $output->writeln('<info>Quiz done!</info>');
        $output->writeln('');

        var_dump($quiz->getResults()); die; // debug-jm
        $output->writeln('');
    }

    protected function init()
    {
        $this->categoriesDir = __DIR__ . '/../../../questions/';
        $this->dialog = new DialogHelper();
        $this->progress = array();
        $this->yaml = new Parser();
    }

    protected function loadCategoriesForQuiz(Quiz $quiz, $categories)
    {
        // If the user passed categories, we'll parse and set them here.
        if ($categories) {
            $categories = explode(',', $categories);
            $categories = array_map(function ($n) {
                return sprintf('%s.yml', trim($n));
            }, $categories);
        } else {
            // Otherwise, we'll grab all available categories.
            $categories = scandir($this->categoriesDir);
        }

        foreach ($categories as $file) {
            $target = $this->categoriesDir . $file;
            if (file_exists($target)) {
                if (preg_match('/.+\.yml/', $file)) {
                    $quiz->addCategory($this->yaml->parse(file_get_contents($target)));
                }
            }
        }
    }

    protected function outputResults(Quiz $quiz, OutputInterface $output)
    {
        $results = $quiz->getResults();
        $output->writeln(sprintf('<comment>Results: %d correct, %d incorrect, %d%s</comment>',
            $results['correct'], $results['incorrect'], $results['score'], '%'
        ));
    }
}
