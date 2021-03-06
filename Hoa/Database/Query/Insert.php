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

namespace Hoa\Database\Query;

/**
 * Class \Hoa\Database\Query\Insert.
 *
 * Build an INSERT query.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2015 Ivan Enderlin.
 * @license    New BSD License
 */

class Insert implements Dml {

    /**
     * Source.
     *
     * @var \Hoa\Database\Query\Insert string
     */
    protected $_into          = null;

    /**
     * Alternative to INSERT.
     *
     * @var \Hoa\Database\Query\Insert string
     */
    protected $_or            = null;

    /**
     * Columns.
     *
     * @var \Hoa\Database\Query\Insert array
     */
    protected $_columns       = [];

    /**
     * Values (tuples).
     *
     * @var \Hoa\Database\Query\Insert array
     */
    protected $_values        = [];

    /**
     * Whether we should use default values or not.
     *
     * @var \Hoa\Database\Query\Insert bool
     */
    protected $_defaultValues = false;



    /**
     * Set source.
     *
     * @access  public
     * @param   string  $name    Name.
     * @return  \Hoa\Database\Query\Insert
     */
    public function into ( $name ) {

        $this->_into = $name;

        return $this;
    }

    /**
     * Insert or rollback.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function rollback ( ) {

        return $this->_or('ROLLBACK');
    }

    /**
     * Insert or abort.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function abort ( ) {

        return $this->_or('ABORT');
    }

    /**
     * Insert or replace.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function replace ( ) {

        return $this->_or('REPLACE');
    }

    /**
     * Insert or fail.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function fail ( ) {

        return $this->_or('FAIL');
    }

    /**
     * Insert or ignore.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function ignore ( ) {

        return $this->_or('IGNORE');
    }

    /**
     * Declare an alternative to “INSERT”.
     *
     * @access  protected
     * @param   string  $or    Alternative.
     * @return  \Hoa\Database\Query\Insert
     */
    protected function _or ( $or ) {

        $this->_or = $or;

        return $this;
    }

    /**
     * Set columns.
     *
     * @access  public
     * @param   string  $column    Column name.
     * @param   ...     ...
     * @return  \Hoa\Database\Query\Insert
     */
    public function on ( $column ) {

        foreach(func_get_args() as $column)
            $this->_columns[] = $column;

        return $this;
    }

    /**
     * Set values (on call per tuple).
     * Expression can be: a regular value or a SELECT query.
     *
     * @access  public
     * @param   mixed  $expression    Expression.
     * @param   ...    ...
     * @return  \Hoa\Database\Query\Insert
     */
    public function values ( $expression ) {

        if($expression instanceof Select)
            $this->_values = (string) $expression;
        else {

            if(is_string($this->_values))
                $this->_values = [];

            $values = &$this->_values[];
            $values = [];

            foreach(func_get_args() as $expression)
                $values[] = $expression;
        }

        return $this;
    }

    /**
     * Use default values.
     *
     * @access  public
     * @return  \Hoa\Database\Query\Insert
     */
    public function defaultValues ( ) {

        $this->_defaultValues = true;

        return $this;
    }

    /**
     * Allow to use the “or” attribute to chain method calls.
     *
     * @access  public
     * @param   string  $name    Name.
     * @return  mixed
     */
    public function __get ( $name ) {

        switch(strtolower($name)) {

            case 'or':
                return $this;
              break;

            default:
                return $this->$name;
        }
    }

    /**
     * Generate the query.
     *
     * @access  public
     * @return  string
     */
    public function __toString ( ) {

        $out = 'INSERT';

        if(null !== $this->_or)
            $out .= ' OR ' . $this->_or;

        $out .= ' INTO ' . $this->_into;

        if(true === $this->_defaultValues)
            return $out . ' DEFAULT VALUES';

        if(!empty($this->_columns))
            $out .= ' (' . implode(', ', $this->_columns) . ')';

        if(is_string($this->_values))
            return $out . ' ' . $this->_values;

        $tuples = [];

        foreach($this->_values as $tuple)
            $tuples[] = '(' . implode(', ', $tuple) . ')';

        return $out . ' VALUES ' . implode(', ', $tuples);
    }
}
