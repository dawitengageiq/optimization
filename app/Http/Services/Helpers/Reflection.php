<?php

namespace App\Http\Services\Helpers;

use ReflectionMethod;

final class Reflection
{
    /**
     * Intantiate.
     */
    public function __construct()
    {
    }

    public function className($class)
    {
        $this->class = $class;

        $this->setMethods();

    }

    public function setMethods($class = '')
    {
        if ($class) {
            $this->methods = get_class_methods($class);
        } else {
            $this->methods = get_class_methods($this->class);
        }
    }

    public function printDetails($class)
    {
        if (is_string($class)) {
            echo $class.'<br />';
        }
        if (is_object($class)) {
            echo get_class($class).'<br />';
        }
        $this->className($class);

        foreach ($this->methods as $method) {
            $this->printDocComment($method);
            $this->printMethodArgumentsAndDetails($method);
        }
    }

    public function printDocCommentOnEachMethod()
    {
        foreach ($this->methods as $method) {
            $reflection = new ReflectionMethod($this->class, $method);
            printR($reflection->getDocComment());
            echo '<h4><span style="color: #888;">Method name:</span> <span style="font-style: italic; color: #0086b3; font-weight: normal;">'.$method.'</span></h4>';
        }
    }

    public function printDocComment($method)
    {
        $reflection = new ReflectionMethod($this->class, $method);
        printR($reflection->getDocComment());
        echo '<h4><span style="color: #888;">Method name:</span> <span style="font-style: italic; color: #0086b3; font-weight: normal;">'.$method.'</span></h4>';
    }

    public function printMethodArgumentsAndDetails($method)
    {
        $reflection = new ReflectionMethod($this->class, $method);

        $count = 1;
        foreach ($reflection->getParameters() as $param) {
            //$param is an instance of ReflectionParameter
            echo '<span style="color: #888;">Argument '.$count.' :</span>';
            echo ' <span style="font-style: italic; color: #0086b3; font-weight: normal;">'.$param->getName().'</span>';
            echo ' - <span style="color: #d14; font-weight: normal;">'.(($param->isOptional()) ? 'optional' : 'required').'</span> | ';
            echo ' <span style="color: #888;">Type:</span> <span style="color: #458;">'.$param->getType().'</span><br />';
            $count++;
        }
    }

    public function printMethods()
    {
        foreach ($this->methods as $method) {
            printR('Method name: '.$method);
        }
    }

    public function methods()
    {
        return $this->methods;
    }
}
