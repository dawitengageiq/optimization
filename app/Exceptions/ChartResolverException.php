<?php

namespace App\Exceptions;

use Exception;

class ChartResolverException extends Exception
{
    protected $id;

    protected $details;

    public function __construct($message)
    {
        $message = $this->create(func_get_args());
        parent::__construct($message);
    }

    protected function create(array $args)
    {
        $this->id = array_shift($args);
        $error = $this->errors($this->id);
        $this->details = vsprintf($error['context'], $args);

        return $this->details;
    }

    protected function errors($id)
    {
        $data = [
            'no_graph' => [
                'context' => 'Sorry, the requested resource could not be found for there\'s no graph related to it.'."\n\r",
            ],
        ];

        return $data[$id];
    }
}
