<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2015, Ivan Enderlin. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Realdom\Bin {

/**
 * Class \Hoa\Realdom\Bin\Reflection.
 *
 * Show informations about a realistic domain.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class Reflection extends \Hoa\Console\Dispatcher\Kit {

    /**
     * Options description.
     *
     * @var \Hoa\Realdom\Bin\Reflection array
     */
    protected $options = array(
        array('list', \Hoa\Console\GetOption::NO_ARGUMENT, 'l'),
        array('help', \Hoa\Console\GetOption::NO_ARGUMENT, 'h'),
        array('help', \Hoa\Console\GetOption::NO_ARGUMENT, '?')
    );



    /**
     * The entry method.
     *
     * @access  public
     * @return  int
     */
    public function main ( ) {

        $list = false;

        while(false !== $c = $this->getOption($v)) switch($c) {

            case 'l':
                $list = $v;
              break;

            case 'h':
            case '?':
                return $this->usage();
              break;

            case '__ambiguous':
                $this->resolveOptionAmbiguity($v);
              break;
        }

        $matches = array();

        from('Hoathis or Hoa')
        -> foreachImport('Realdom.*', function ( $classname ) use ( &$matches ) {

            $class = new \ReflectionClass($classname);

            if($class->isSubclassOf('\Hoa\Realdom'))
                $matches[$classname::NAME] = $class;

            return;
        });

        if(true === $list) {

            echo implode("\n", array_keys($matches)), "\n";

            return;
        }

        $this->parser->listInputs($realdom);

        if(empty($realdom))
            return $this->usage();

        if(!isset($matches[$realdom]))
            throw new \Hoa\Console\Exception(
                'The %s realistic domain does not exist.',
                0, $realdom);

        $class      = $matches[$realdom];
        $object     = $class->newInstanceWithoutConstructor();
        $_arguments = $class->getProperty('_arguments');
        $_arguments->setAccessible(true);
        $arguments  = $_arguments->getValue($object);

        echo 'Realdom ', $realdom, ' {', "\n\n",
             '    Implementation ', $class->getName(), ';', "\n\n",
             '    Parent ', $class->getParentClass()->getName(), ';', "\n\n",
             '    Interfaces {', "\n\n";

        $interfaces = $class->getInterfaces();
        usort($interfaces, function ( $a, $b ) {

            if('' === $a->getNamespaceName())
                if('' === $b->getNamespaceName())
                    return strcmp($a->getName(), $b->getName());
                else
                    return -1;

            if('' === $b->getNamespaceName())
                return 1;

            return strcmp($a->getName(), $b->getName());
        });

        foreach($interfaces as $interface)
            echo '        ', $interface->getName(), ';', "\n";

        echo '    }', "\n\n",
             '    Parameters {', "\n\n";

        $i = 0;

        if(is_array($arguments))
            foreach($arguments as $typeAndName => $defaultValue) {

                if(is_int($typeAndName)) {

                    $typeAndName  = $defaultValue;
                    $defaultValue = null;
                }

                echo '        [#', $i++,
                     ' ', (null === $defaultValue ? 'required' : 'optional'), '] ',
                     $typeAndName;

                if(null !== $defaultValue)
                    echo ' = ', var_export($defaultValue, true);

                echo ';', "\n";
            }
        else
            echo '        …variadic', "\n";

        echo '    }', "\n", '}', "\n";

        return;
    }

    /**
     * The command usage.
     *
     * @access  public
     * @return  int
     */
    public function usage ( ) {

        echo 'Usage   : realdom:reflection <options> [realdom]', "\n",
             'Options :', "\n",
             $this->makeUsageOptionsList(array(
                 'l'    => 'List all realdoms.',
                 'help' => 'This help.'
             )), "\n";

        return;
    }
}

}

__halt_compiler();
Show informations about a realistic domain.
