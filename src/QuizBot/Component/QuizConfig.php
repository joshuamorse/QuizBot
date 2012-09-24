<?php

namespace QuizBot\Component;

class QuizConfig
{
    protected $return;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get($value)
    {
        $return = false; 

        if ($this->return === null) {
            if (isset($this->config[$value])) {
                $return = $this->config[$value];
            }
        } else {
            if (isset($this->return[$value])) {
                $return = $this->return[$value];
            }
        }

        $this->return = $return;

        return $this;
    }

    public function execute()
    {
        return $this->return;
    }
}
