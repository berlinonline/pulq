<?php

/**
  * @class UserService_SQL
  *
  * Primary goal of this class is to offer an SQL-Builder which protects the programmer from involuntary sql-injection flows
  *
  *
  * @package UserService
  * @subpackage Core
  * @author Mathias Fischer <mathias.fischer@berlinonline.de>
  * @copyright BerlinOnline Stadtportal GmbH & Co. KG
  * @version $id$
  */



class UserService_SQL
{
    private $cols ;
    private $suchbegriff;
    private $sortierennach;
    private $ergebnisfeld;
    private $ergebnis = false;

    private $datenbank;
    private $from;
    private $join = array();
    private $where = array();
    private $select = array();
    private $values = array();
    public $sqlStatement;
    private $orderBy;
    private $limit;

    public function __construct ()
    {
        $this->sqlStatement = 'SELECT';
    }

    public static function select ()
    {
        return new UserService_SQL();
    }

    public static function insert ()
    {
        $sql = new UserService_SQL();
        $sql->sqlStatement = 'INSERT';
        return $sql;
    }

    public static function update ()
    {
        $sql = new UserService_SQL();
        $sql->sqlStatement = 'UPDATE';
        return $sql;
    }


    public static function replace ()
    {
        $sql = new UserService_SQL();
        $sql->sqlStatement = 'REPLACE';
        return $sql;
    }

    public static function delete ()
    {
        $sql = new UserService_SQL();
        $sql->sqlStatement = 'DELETE';
        return $sql;
    }



    public static function escape ($string)
    {
        $elements = explode('.', $string);
        foreach ($elements as $key => $element)
        {
            if ('*' !== $element)
            {
                $elements[$key] = "`".mysql_escape_string($element)."`";
            }
        }
        $escaped = implode('.', $elements);
        return $escaped;
    }

    public static function quote ($string)
    {
        if (NULL !== $string)
        {
            return "'".mysql_escape_string($string)."'";
        }
        return '';
    }

    public function into ($indertabelle)
    {
        return $this->from($indertabelle);
    }

    public function from ($indertabelle)
    {
        $this->from = self::escape($indertabelle);
        return $this;
    }

    public function join($table, $left = NULL, $right = NULL)
    {
        $this->join[] = array(
            self::escape($table),
            $left ? self::escape($left) : NULL,
            $right ? self::escape($right) : NULL
        );
        return $this;
    }


    public function addValue ($column, $value)
    {
        $this->values[] = array($column, $value);
        return $this;
    }

    public function addValues (array $values)
    {
        foreach ($values as $key => $value)
        {
            $this->addValue($key, $value);
        }
        return $this;
    }

    protected function addQuotedWhere ($column, $condition, $and_or = 'AND', $operator = '=')
    {
        if (NULL !== $condition || in_array($operator, array('IS NULL', 'IS NOT NULL')) )
        {
            $where = self::escape($column)." ".$operator." ".self::quote($condition);
            $this->where[] = array($and_or, $where);
        }
        else
        {
            $this->where[] = array($and_or, self::escape($column));
        }
        return $this;
    }

    public function andWhere ($column, $condition = NULL)
    {
        $this->addQuotedWhere($column, $condition, 'AND');
        return $this;
    }


    public function orWhere ($column, $condition = NULL)
    {
        $this->addQuotedWhere($column, $condition, 'OR');
        return $this;
    }

    public function andWhereNull ($column)
    {
        $this->addQuotedWhere($column, NULL, 'AND', 'IS NULL');
        return $this;
    }

    public function orWhereNull ($column)
    {
        $this->addQuotedWhere($column, NULL, 'OR', 'IS NULL');
        return $this;
    }

    public function addWhereIn ($column, array $values, $and_or = 'AND')
    {
        if (count($values))
        {
            $values = array_map("mysql_real_escape_string", $values);
            $valueCSV = implode(',', $values);
            $column = self::escape($column);
            $where = "$column IN ($valueCSV)";
            $this->where[] = array($and_or, $where);
        }
        return $this;
    }

    public function andWhereIn ($column, array $values)
    {
        $this->addWhereIn($column, $values, 'AND');
        return $this;
    }

    public function startWhereBlock($and_or = 'AND')
    {
        $this->where[] = array($and_or, "(");
        return $this;
    }

    public function stopWhereBlock()
    {
        $this->where[] = array("", ")");
        return $this;
    }

    public function andSearch (array $columns, $term, $and_or = 'OR')
    {
        $this->startWhereBlock();
        foreach ($columns as $column)
        {
            $this->addQuotedWhere($column, $term, $and_or, 'LIKE');
        }
        $this->stopWhereBlock();
        return $this;
    }

    public function addSelect ($select, $as = NULL)
    {
        if (NULL !== $as)
        {
            $this->select[] = self::escape($select).' AS '.self::escape($as);
        }
        else
        {
            $this->select[] = self::escape($select);
        }
        return $this;
    }

    public function addSelectList ($selects)
    {
        foreach ($selects as $select)
        {
            $this->addSelect($select);
        }
        return $this;
    }

    public function orderBy ($order)
    {
        $this->orderBy = self::escape($order);
        return $this;
    }

    public function limit ($num)
    {
        $this->limit = intval($num);
        return $this;
    }

    public function buildSelect()
    {
        if (!count($this->select))
        {
            return '*';
        }
        return implode(', ', $this->select);
    }

    public function buildJoin()
    {
        $join = '';
        foreach ($this->join as $joinelement)
        {
            list($table, $left, $right) = $joinelement;
            $join .= ' LEFT JOIN '.$table;
            if ($left && NULL === $right)
            {
                $join .= " USING($left)";
            }
            elseif ($left && $right)
            {
                $join .= " ON $left = $right";
            }
        }
        return $join;
    }

    public function buildWhere()
    {
        $where = '';
        $addSeperator = FALSE;
        foreach ($this->where as $whereClause)
        {
            list ($seperator, $whereStatement) = $whereClause;
            if ($addSeperator)
            {
                $where .= " $seperator ";
            }
            else
            {
                $addSeperator = TRUE;
            }
            $where .= $whereStatement;
            if ($whereStatement === '(')
            {
                $addSeperator = FALSE;
            }
        }
        $where = ($where) ? $where : "1";
        return $where;
    }

    public function buildValues()
    {
        $values = array();
        foreach ($this->values as $value)
        {
            if ($value[1])
            {
                $set = self::escape($value[0]).'='.self::quote($value[1]);
                $values[] = $set;
            }
        }
        return implode(',', $values);
    }

    public function getQuery()
    {
        $where = $this->buildWhere();
        $from  = $this->from;
        $limit = ($this->limit) ? " LIMIT ".$this->limit : "";
        if ($this->sqlStatement === 'DELETE')
        {
            $join = count($this->join) ? "USING $from ".$this->buildJoin() : '';
            $query = "DELETE FROM $from $join WHERE $where $limit";
        }
        elseif (count($this->values) && $this->sqlStatement === 'UPDATE')
        {
            $values = $this->buildValues();
            $query = "UPDATE $from SET $values WHERE $where $limit";
        }
        elseif (count($this->values) && $this->sqlStatement)
        {
            $query = $this->sqlStatement." INTO $from SET ".$this->buildValues();
        }
        else
        {
            $select = $this->buildSelect();
            $join = $this->buildJoin();
            $orderBy = ($this->orderBy) ? " ORDER BY ".$this->orderBy : "";
            $query = "SELECT $select FROM $from $join WHERE $where $orderBy $limit";
        }
        return $query;
    }

    public function __toString()
    {
        return $this->getQuery();
    }
}


?>
