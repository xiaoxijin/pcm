<?php
/**
 * 查询数据库的封装类，基于底层数据库封装类，实现SQL生成器
 * 注：仅支持MySQL，不兼容其他数据库的SQL语法
 * @author Xijin.Xiao
 */
namespace DB;
class Adapter extends  \Common
{

    public $_table_alias = null;

    /**
     * INNER JOIN type.
     */
    const INNER_JOIN = "INNER JOIN";

    /**
     * LEFT JOIN type.
     */
    const LEFT_JOIN = "LEFT JOIN";

    /**
     * RIGHT JOIN type.
     */
    const RIGHT_JOIN = "RIGHT JOIN";

    /**
     * AND logical operator.
     */
    const LOGICAL_AND = "AND";

    /**
     * OR logical operator.
     */
    const LOGICAL_OR = "OR";

    /**
     * Equals comparison operator.
     */
    const EQUALS = "=";

    /**
     * Not equals comparison operator.
     */
    const NOT_EQUALS = "!=";

    /**
     * Less than comparison operator.
     */
    const LESS_THAN = "<";

    /**
     * Less than or equal to comparison operator.
     */
    const LESS_THAN_OR_EQUAL = "<=";

    /**
     * Greater than comparison operator.
     */
    const GREATER_THAN = ">";

    /**
     * Greater than or equal to comparison operator.
     */
    const GREATER_THAN_OR_EQUAL = ">=";

    /**
     * IN comparison operator.
     */
    const IN = "IN";

    /**
     * NOT IN comparison operator.
     */
    const NOT_IN = "NOT IN";

    /**
     * LIKE comparison operator.
     */
    const LIKE = "LIKE";

    /**
     * NOT LIKE comparison operator.
     */
    const NOT_LIKE = "NOT LIKE";

    /**
     * ILIKE comparison operator.
     */
    const ILIKE = "ILIKE";

    /**
     * REGEXP comparison operator.
     */
    const REGEX = "REGEXP";

    /**
     * NOT REGEXP comparison operator.
     */
    const NOT_REGEX = "NOT REGEXP";

    /**
     * BETWEEN comparison operator.
     */
    const BETWEEN = "BETWEEN";

    /**
     * NOT BETWEEN comparison operator.
     */
    const NOT_BETWEEN = "NOT BETWEEN";

    /**
     * IS comparison operator.
     */
    const IS = "IS";

    /**
     * IS NOT comparison operator.
     */
    const IS_NOT = "IS NOT";

    /**
     * Ascending ORDER BY direction.
     */
    const ORDER_BY_ASC = "ASC";

    /**
     * Descending ORDER BY direction.
     */
    const ORDER_BY_DESC = "DESC";

    /**
     * Open bracket for grouping criteria.
     */
    const BRACKET_OPEN = "(";

    /**
     * Closing bracket for grouping criteria.
     */
    const BRACKET_CLOSE = ")";

    /**
     * PDO database connection to use in executing the statement.
     *
     * @var PDO|null
     */
    private $PdoConnection;

    /**
     * Whether to automatically escape values.
     *
     * @var bool|null
     */
    private $autoQuote;

    /**
     * Execution options like DISTINCT and SQL_CALC_FOUND_ROWS.
     *
     * @var array
     */
    private $option;

    /**
     * Columns, tables, and expressions to SELECT from.
     *
     * @var array
     */
    private $select;

    /**
     * Table to INSERT into.
     *
     * @var string
     */
    private $insert;

    /**
     * Table to REPLACE into.
     *
     * @var string
     */
    private $replace;

    /**
     * Table to UPDATE.
     *
     * @var string
     */
    private $update;

    /**
     * Tables to DELETE from, or true if deleting from the FROM table.
     *
     * @var array|true
     */
    private $delete;

    /**
     * Column values to INSERT, UPDATE, or REPLACE.
     *
     * @var array
     */
    private $set;

    /**
     * Table to select FROM.
     *
     * @var array
     */
    private $from;

    /**
     * JOIN tables and ON criteria.
     *
     * @var array
     */
    private $join;

    /**
     * WHERE criteria.
     *
     * @var array
     */
    private $where;

    /**
     * Columns to GROUP BY.
     *
     * @var array
     */
    private $groupBy;

    /**
     * HAVING criteria.
     *
     * @var array
     */
    private $having;

    /**
     * Columns to ORDER BY.
     *
     * @var array
     */
    private $orderBy;

    /**
     * Number of rows to return from offset.
     *
     * @var array
     */
    private $limit;

    /**
     * SET placeholder values.
     *
     * @var array
     */
    private $setPlaceholderValues;

    /**
     * WHERE placeholder values.
     *
     * @var array
     */
    private $wherePlaceholderValues;

    /**
     * HAVING placeholder values.
     *
     * @var array
     */
    private $havingPlaceholderValues;

    /**
     * Constructor.
     *
     * @param  PDO|null $PdoConnection optional PDO database connection
     * @param  bool $autoQuote optional auto-escape values, default true
     * @return Adapter
     */
//    public function __construct(\PDO $PdoConnection = null, $autoQuote = true) {
//        $this->option = array();
//        $this->select = array();
//        $this->delete = array();
//        $this->set = array();
//        $this->from = array();
//        $this->join = array();
//        $this->where = array();
//        $this->groupBy = array();
//        $this->having = array();
//        $this->orderBy = array();
//        $this->limit = array();
//
//        $this->setPlaceholderValues = array();
//        $this->wherePlaceholderValues = array();
//        $this->havingPlaceholderValues = array();
//
//        $this->setPdoConnection($PdoConnection)
//            ->setAutoQuote($autoQuote);
//    }


    /**
     * Set the PDO database connection to use in executing this statement.
     *
     * @param  PDO|null $PdoConnection optional PDO database connection
     * @return Adapter
     */
    protected function setPdoConnection(\PDO $PdoConnection = null) {
        $this->PdoConnection = $PdoConnection;

        return $this;
    }

    /**
     * Get the PDO database connection to use in executing this statement.
     *
     * @return PDO|null
     */
    protected function getPdoConnection() {
        return $this->PdoConnection;
    }

    /**
     * Set whether to automatically escape values.
     *
     * @param  bool|null $autoQuote whether to automatically escape values
     * @return Adapter
     */
    protected function setAutoQuote($autoQuote) {
        $this->autoQuote = $autoQuote;

        return $this;
    }

    /**
     * Get whether values will be automatically escaped.
     *
     * The $override parameter is for convenience in checking if a specific
     * value should be quoted differently than the rest. 'null' defers to the
     * global setting.
     *
     * @param  bool|null $override value-specific override for convenience
     * @return bool
     */
    protected function getAutoQuote($override = null) {
        return $override === null ? $this->autoQuote : $override;
    }

    /**
     * Safely escape a value if auto-quoting is enabled, or do nothing if
     * disabled.
     *
     * The $override parameter is for convenience in checking if a specific
     * value should be quoted differently than the rest. 'null' defers to the
     * global setting.
     *
     * @param  mixed $value value to escape (or not)
     * @param  bool|null $override value-specific override for convenience
     * @return mixed|false value (escaped or original) or false if failed
     */
    protected function autoQuote($value, $override = null) {
        return $this->getAutoQuote($override) ? $this->quote($value) : $value;
    }

    /**
     * Safely escape a value for use in a statement.
     *
     * @param  mixed $value value to escape
     * @return mixed|false escaped value or false if failed
     */
    protected function quote($value) {
//        $PdoConnection = $this->getPdoConnection();

        // If a PDO database connection is set, use it to quote the value using
        // the underlying database. Otherwise, quote it manually.
//        if ($PdoConnection) {
//            return $PdoConnection->quote($value);
//        }
//        else
        if (is_numeric($value)) {
            return $value;
        }
        elseif (is_null($value)) {
            return "NULL";
        }
        else {
            return "'" . addslashes($value) . "'";
        }
    }

    /**
     * Add an execution option like DISTINCT or SQL_CALC_FOUND_ROWS.
     *
     * @param  string $option execution option to add
     * @return Adapter
     */
    protected function option($option) {
        $this->option[] = $option;

        return $this;
    }

    /**
     * Get the execution options portion of the statement as a string.
     *
     * @param  bool $includeTrailingSpace optional include space after options
     * @return string execution options portion of the statement
     */
    protected function getOptionsString($includeTrailingSpace = false) {
        $statement = "";

        if (!$this->option) {
            return $statement;
        }

        $statement .= implode(' ', $this->option);

        if ($includeTrailingSpace) {
            $statement .= " ";
        }

        return $statement;
    }



    /**
     * Add SQL_CALC_FOUND_ROWS execution option.
     *
     * @return Adapter
     */
    protected function calcFoundRows() {
        return $this->option('SQL_CALC_FOUND_ROWS');
    }

    /**
     * Add DISTINCT execution option.
     *
     * @return Adapter
     */
    protected function distinct() {
        return $this->option('DISTINCT');
    }

    /**
     * Add a SELECT column, table, or expression with optional alias.
     *
     * @param  string $column column name, table name, or expression
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function select($column, $alias = null) {
        if(!strstr($column,'.'))
            $column = $this->getColumnPrefix().$column;
        $this->select[$column] = $alias;
        return $this;
    }

    protected function concat($column, $alias = null) {
        if(is_array($column))
            $column = implode(',',$column);
        $this->select['concat'.self::BRACKET_OPEN.$column.self::BRACKET_CLOSE] = $alias;
        return $this;
    }


    /**
     * Get the SELECT portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'SELECT' text, default true
     * @return string SELECT portion of the statement
     */
    protected function getSelectString($includeText = true) {
        $statement = "";

        if (!$this->select) {
            return $statement;
        }

        $statement .= $this->getOptionsString(true);

        foreach ($this->select as $column => $alias) {
            $statement .= $column;

            if ($alias) {
                $statement .= " AS " . $alias;
            }

            $statement .= ", ";
        }

        $statement = substr($statement, 0, -2);

        if ($includeText && $statement) {
            $statement = "SELECT " . $statement;
        }

        return $statement;
    }

    /**
     * Set the INSERT table.
     *
     * @param  string $table INSERT table
     * @return Adapter
     */
    protected function insert($table) {
        $this->insert = $table;

        return $this;
    }


    /**
     * Get the INSERT table.
     *
     * @return string INSERT table
     */
    protected function getInsert() {
        return $this->insert;
    }

    /**
     * Get the INSERT portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'INSERT' text, default true
     * @return string INSERT portion of the statement
     */
    protected function getInsertString($includeText = true) {
        $statement = "";

        if (!$this->insert) {
            return $statement;
        }

        $statement .= $this->getOptionsString(true);

        $statement .= $this->getInsert();

        if ($includeText && $statement) {
            $statement = "INSERT " . $statement;
        }

        return $statement;
    }

    /**
     * Set the REPLACE table.
     *
     * @param  string $table REPLACE table
     * @return Adapter
     */
    protected function replace($table) {
        $this->replace = $table;

        return $this;
    }



    /**
     * Get the REPLACE table.
     *
     * @return string REPLACE table
     */
    protected function getReplace() {
        return $this->replace;
    }

    /**
     * Get the REPLACE portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'REPLACE' text, default true
     * @return string REPLACE portion of the statement
     */
    protected function getReplaceString($includeText = true) {
        $statement = "";

        if (!$this->replace) {
            return $statement;
        }

        $statement .= $this->getOptionsString(true);

        $statement .= $this->getReplace();

        if ($includeText && $statement) {
            $statement = "REPLACE " . $statement;
        }

        return $statement;
    }

    /**
     * Set the UPDATE table.
     *
     * @param  string $table UPDATE table
     * @return Adapter
     */

    protected function update($table, $alias = null) {
        $this->update['table'] = $table;
        if(!$alias && $this->_table_alias)
            $alias =$this->_table_alias;
        $this->update['alias'] = $alias;
        return $this;
    }

    /**
     * Get the FROM table.
     *
     * @return string FROM table
     */
    protected function getUpdateTable() {
        return $this->update['table'];
    }

    /**
     * Get the FROM table alias.
     *
     * @return string FROM table alias
     */
    protected function getUpdateAlias() {
        return $this->update['alias'];
    }



    /**
     * Get the UPDATE table.
     *
     * @return string UPDATE table
     */
    protected function getUpdate() {
        $statement = "";
        if (!$this->update) {
            return $statement;
        }
        $statement .= $this->getUpdateTable();
        if ($this->getUpdateAlias()) {
            $statement .= " AS " . $this->getUpdateAlias();
        }
        return $statement;
    }

    /**
     * Get the UPDATE portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'UPDATE' text, default true
     * @return string UPDATE portion of the statement
     */
    protected function getUpdateString($includeText = true) {
        $statement = "";

        if (!$this->update) {
            return $statement;
        }

        $statement .= $this->getOptionsString(true);

        $statement .= $this->getUpdate();

        // Add any JOINs.
        $statement .= " " . $this->getJoinString();

        $statement  = rtrim($statement);

        if ($includeText && $statement) {
            $statement = "UPDATE " . $statement;
        }

        return $statement;
    }

    /**
     * Add a table to DELETE from, or false if deleting from the FROM table.
     *
     * @param  string|false $table optional table name, default false
     * @return Adapter
     */
    protected function delete($table = false) {
        if ($table === false) {
            $this->delete = true;
        }
        else {
            // Reset the array in case the class variable was previously set to a
            // boolean value.
            if (!is_array($this->delete)) {
                $this->delete = array();
            }

            $this->delete[] = $table;
        }

        return $this;
    }


    /**
     * Get the DELETE portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'DELETE' text, default true
     * @return string DELETE portion of the statement
     */
    protected function getDeleteString($includeText = true) {
        $statement = "";

        if (!$this->delete && !$this->isDeleteTableFrom()) {
            return $statement;
        }

        $statement .= $this->getOptionsString(true);

        if (is_array($this->delete)) {
            $statement .= implode(', ', $this->delete);
        }

        if ($includeText && ($statement || $this->isDeleteTableFrom())) {
            $statement = "DELETE " . $statement;

            // Trim in case the table is specified in FROM.
            $statement = trim($statement);
        }

        return $statement;
    }

    /**
     * Whether the FROM table is the single table to delete from.
     *
     * @return bool whether the delete table is FROM
     */
    protected function isDeleteTableFrom() {
        return $this->delete === true;
    }

    /**
     * Add one or more column values to INSERT, UPDATE, or REPLACE.
     *
     * @param  string|array $column column name or array of columns => values
     * @param  mixed|null $value optional value for single column
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function set($column, $value = null, $quote = true) {
        if (is_array($column)) {
            foreach ($column as $columnName => $columnValue) {
                $this->set($columnName, $columnValue, $quote);
            }
        }
        else {

            $this->set[] = array('column' => $column,
                'value'  => $value,
                'quote'  => $quote);
        }

        return $this;
    }

    protected function increment($column, $value = null) {
        if(!strstr($column,'.'))
            $column = $this->getColumnPrefix().$column;
        $value=$column.$value;
        return $this->set($column, $value,null);
    }

    /**
     * Add an array of columns => values to INSERT, UPDATE, or REPLACE.
     *
     * @param  array $values columns => values
     * @return Adapter
     */
    protected function values(array $values) {
        return $this->set($values);
    }



    /**
     * Get the SET portion of the statement as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'SET' text, default true
     * @return string SET portion of the statement
     */
    protected function getSetString($usePlaceholders = true, $includeText = true) {
        $statement = "";
        $this->setPlaceholderValues = array();


        foreach ($this->set as $set) {
            $autoQuote = $this->getAutoQuote($set['quote']);

            if(!$this->isInsert() && !strstr($set['column'],'.'))
                $set['column'] = $this->getColumnPrefix().$set['column'];

            if ($usePlaceholders && $autoQuote) {
                $statement .= $set['column'] . " " . self::EQUALS . " ?, ";

                $this->setPlaceholderValues[] = $set['value'];
            }
            else {
                $statement .= $set['column'] . " " . self::EQUALS . " " . $this->autoQuote($set['value'], $autoQuote) . ", ";
            }
        }

        $statement = substr($statement, 0, -2);

        if ($includeText && $statement) {
            $statement = "SET " . $statement;
        }

        return $statement;
    }

    /**
     * Get the SET placeholder values.
     *
     * @return array SET placeholder values
     */
    protected function getSetPlaceholderValues() {
        return $this->setPlaceholderValues;
    }

    /**
     * Set the FROM table with optional alias.
     *
     * @param  string $table table name
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function from($table, $alias = null) {
        $this->from['table'] = $table;
        if(!$alias && $this->_table_alias)
            $alias =$this->_table_alias;
        $this->from['alias'] = $alias;
        return $this;
    }


    /**
     * Get the FROM table.
     *
     * @return string FROM table
     */
    protected function getFrom() {
        return $this->from['table'];
    }

    /**
     * Get the FROM table alias.
     *
     * @return string FROM table alias
     */
    protected function getFromAlias() {
        return $this->from['alias'];
    }

    /**
     * Whether the join table and alias is unique (hasn't already been joined).
     *
     * @param  string $table table name
     * @param  string $alias table alias
     * @return bool whether the join table and alias is unique
     */
    protected function isJoinUnique($table, $alias) {
        foreach ($this->join as $join) {
            if ($join['table'] == $table && $join['alias'] == $alias) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $type optional type of join, default INNER JOIN
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function join($table, $criteria = null, $type = self::INNER_JOIN, $alias = null,$associate='previous') {
        if (!$this->isJoinUnique($table, $alias)) {
            return $this;
        }

        $criteria_flag=[];
        if (is_string($criteria)) {
            $criteria_flag = array($criteria);
        }elseif (is_array($criteria) && !isset($criteria[0])){
            $criteria_flag[]=$criteria;
        }else
            $criteria_flag = $criteria;

        $this->join[] = array('table'    => $table,
            'criteria'  => $criteria_flag,
            'type'      => $type,
            'alias'     => $alias,
            'associate' => $associate
        );

        return $this;
    }

    /**
     * Add an INNER JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function innerJoin($table, $criteria = null, $alias = null,$associate='previous') {
        return $this->join($table, $criteria, self::INNER_JOIN, $alias,$associate);
    }

    /**
     * Add a LEFT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function leftJoin($table, $criteria = null, $alias = null,$associate='previous') {
        return $this->join($table, $criteria, self::LEFT_JOIN, $alias,$associate);
    }

    /**
     * Add a RIGHT JOIN table with optional ON criteria.
     *
     * @param  string $table table name
     * @param  string|array $criteria optional ON criteria
     * @param  string $alias optional alias
     * @return Adapter
     */
    protected function rightJoin($table, $criteria = null, $alias = null,$associate='previous') {
        return $this->join($table, $criteria, self::RIGHT_JOIN, $alias,$associate);
    }


    protected function getMasterTable(){
        $associateTable='';
        if ($this->isSelect()) {

            if($alias = $this->getFromAlias())
                $associateTable = $alias;
            else
                $associateTable = $this->getFrom();

        }
        elseif ($this->isUpdate()) {
            if($alias = $this->getUpdateAlias())
                $associateTable = $alias;
            else
                $associateTable = $this->getUpdateTable();
        }
        return $associateTable;
    }
    /**
     * Get an ON criteria string joining the specified table and column to the
     * same column of the previous JOIN or FROM table.
     *
     * @param  int $joinIndex index of current join
     * @param  string $table current table name
     * @param  string $column current column name
     * @return string ON join criteria
     */
    protected function getJoinCriteriaUsingAssociateTable($joinIndex, $table, $column,$associate) {
        $joinCriteria = "";
        $previousJoinIndex = $joinIndex - 1;
        //previous
        // If the previous table is from a JOIN, use that. Otherwise, use the
        // FROM table.
        if ($associate=='previous'){
            if(array_key_exists($previousJoinIndex, $this->join))
            {
                if ($this->join[$previousJoinIndex]['alias'])
                    $associateTable = $this->join[$previousJoinIndex]['alias'];
                else
                    $associateTable = $this->join[$previousJoinIndex]['table'];
            }else
                $associateTable = $this->getMasterTable();
        }elseif ($associate=='master'){
            $associateTable = $this->getMasterTable();
        }else{
            $associateTable = $associate;
        }

        // In the off chance there is no previous table.
        if ($associateTable) {
            $joinCriteria .= $associateTable . ".";
        }

        if(is_array($column)){
            $joinCriteria .= key($column) . " " . self::EQUALS . " " . $table . "." . array_shift($column);
        }else
            $joinCriteria .= $column . " " . self::EQUALS . " " . $table . "." . $column;

        return $joinCriteria;
    }

    /**
     * Get the JOIN portion of the statement as a string.
     *
     * @return string JOIN portion of the statement
     */
    protected function getJoinString() {
        $statement = "";
        foreach ($this->join as $i => $join) {
            $statement .= " " . $join['type'] . " " . $join['table'];

            if ($join['alias']) {
                $statement .= " AS " . $join['alias'];
                $table = $join['alias'];
            }else{
                $table = $join['table'];
            }

            // Add ON criteria if specified.
            if ($join['criteria']) {
                $statement .= " ON ";

                foreach ($join['criteria'] as $x => $criterion) {
                    // Logically join each criterion with AND.
                    if ($x != 0) {
                        $statement .= " " . self::LOGICAL_AND . " ";
                    }

                    // If the criterion does not include an equals sign, assume a
                    // column name and join against the same column from the previous
                    // table.
                    if (is_array($criterion)  || strpos($criterion, '=') === false) {

                        $statement .= $this->getJoinCriteriaUsingAssociateTable($i, $table, $criterion,$join['associate']);
                    }
                    else {
                        $statement .= $criterion;
                    }
                }
            }
        }

        $statement = trim($statement);

        return $statement;
    }

    /**
     * Get the FROM portion of the statement, including all JOINs, as a string.
     *
     * @param  bool $includeText optional include 'FROM' text, default true
     * @return string FROM portion of the statement
     */
    protected function getFromString($includeText = true) {
        $statement = "";

        if (!$this->from) {
            return $statement;
        }

        $statement .= $this->getFrom();


        if (!$this->isDelete() && $this->getFromAlias()) {
            $statement .= " AS " . $this->getFromAlias();
            // Add any JOINs.
            $statement .= " " . $this->getJoinString();
        }


        $statement  = rtrim($statement);

        if ($includeText && $statement) {
            $statement = "FROM " . $statement;
        }

        return $statement;
    }

    /**
     * Add an open bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $connector optional logical connector, default AND
     * @return Adapter
     */
    protected function openCriteria(array &$criteria, $connector = self::LOGICAL_AND) {
        $criteria[] = array('bracket'   => self::BRACKET_OPEN,
            'connector' => $connector);

        return $this;
    }
    /**
     * Add a closing bracket for nesting conditions to the specified WHERE or
     * HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @return Adapter
     */
    protected function closeCriteria(array &$criteria) {
        $criteria[] = array('bracket'   => self::BRACKET_CLOSE,
            'connector' => null);

        return $this;
    }

    /**
     * Add a condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function criteria(array &$criteria, $column, $value, $operator = self::EQUALS,
                              $connector = self::LOGICAL_AND, $quote = true) {


        $criteria[] = array('column'    => $column,
            'value'     => $value,
            'operator'  => $operator,
            'connector' => $connector,
            'quote'     => $quote);

        return $this;
    }

    /**
     * Add an OR condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function orCriteria(array &$criteria, $column, $value, $operator = self::EQUALS, $quote = true) {
        return $this->criteria($criteria, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function criteriaIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND,
                                $quote = true) {
        return $this->criteria($criteria, $column, $values, self::IN, $connector, $quote);
    }

    /**
     * Add a NOT IN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function criteriaNotIn(array &$criteria, $column, array $values, $connector = self::LOGICAL_AND,
                                   $quote = true) {
        return $this->criteria($criteria, $column, $values, self::NOT_IN, $connector, $quote);
    }

    /**
     * Add a BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function criteriaBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND,
                                     $quote = true) {
        return $this->criteria($criteria, $column, array($min, $max), self::BETWEEN, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN condition to the specified WHERE or HAVING criteria.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function criteriaNotBetween(array &$criteria, $column, $min, $max, $connector = self::LOGICAL_AND,
                                        $quote = true) {
        return $this->criteria($criteria, $column, array($min, $max), self::NOT_BETWEEN, $connector, $quote);
    }

    /**
     * Get the WHERE or HAVING portion of the statement as a string.
     *
     * @param  array $criteria WHERE or HAVING criteria
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  array $placeholderValues optional placeholder values array
     * @return string WHERE or HAVING portion of the statement
     */
    protected function getCriteriaString(array &$criteria, $usePlaceholders = true,
                                       array &$placeholderValues = array()) {
        $statement = "";
        $placeholderValues = array();

        $useConnector = false;

        foreach ($criteria as $i => $criterion) {
            if (array_key_exists('bracket', $criterion)) {
                // If an open bracket, include the logical connector.
                if (strcmp($criterion['bracket'], self::BRACKET_OPEN) == 0) {
                    if ($useConnector) {
                        $statement .= " " . $criterion['connector'] . " ";
                    }

                    $useConnector = false;
                }
                else {
                    $useConnector = true;
                }

                $statement .= $criterion['bracket'];
            }
            else {
                if ($useConnector) {
                    $statement .= " " . $criterion['connector'] . " ";
                }

                $useConnector = true;
                $autoQuote = $this->getAutoQuote($criterion['quote']);

                switch ($criterion['operator']) {
                    case self::BETWEEN:
                    case self::NOT_BETWEEN:
                        if ($usePlaceholders && $autoQuote) {
                            $value = "? " . self::LOGICAL_AND . " ?";

                            $placeholderValues[] = $criterion['value'][0];
                            $placeholderValues[] = $criterion['value'][1];
                        }
                        else {
                            $value = $this->autoQuote($criterion['value'][0], $autoQuote) . " " . self::LOGICAL_AND . " " .
                                $this->autoQuote($criterion['value'][1], $autoQuote);
                        }

                        break;

                    case self::IN:
                    case self::NOT_IN:
                        if ($usePlaceholders && $autoQuote) {
                            $value = self::BRACKET_OPEN . substr(str_repeat('?, ', count($criterion['value'])), 0, -2) .
                                self::BRACKET_CLOSE;

                            $placeholderValues = array_merge($placeholderValues, $criterion['value']);
                        }
                        else {
                            $value = self::BRACKET_OPEN;

                            foreach ($criterion['value'] as $criterionValue) {
                                $value .= $this->autoQuote($criterionValue, $autoQuote) . ", ";
                            }

                            $value  = substr($value, 0, -2);
                            $value .= self::BRACKET_CLOSE;
                        }

                        break;

                    case self::IS:
                    case self::IS_NOT:
                        $value = $criterion['value'];

                        break;

                    default:
                        if ($usePlaceholders && $autoQuote) {
                            $value = "?";

                            $placeholderValues[] = $criterion['value'];
                        }
                        else {
                            $value = $this->autoQuote($criterion['value'], $autoQuote);
                        }

                        break;
                }

                if(!$this->isDelete() && !strstr($criterion['column'],'.'))
                    $criterion['column'] = $this->getColumnPrefix().$criterion['column'];
                $statement .= $criterion['column'] . " " . $criterion['operator'] . " " . $value;
            }
        }

        return $statement;
    }

    /**
     * Add an open bracket for nesting WHERE conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return Adapter
     */
    protected function openWhere($connector = self::LOGICAL_AND) {
        return $this->openCriteria($this->where, $connector);
    }

    /**
     * Add a closing bracket for nesting WHERE conditions.
     *
     * @return Adapter
     */
    protected function closeWhere() {
        return $this->closeCriteria($this->where);
    }

    /**
     * Add a WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function where($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteria($this->where, $column, $value, $operator, $connector, $quote);
    }


    /**
     * Add an AND WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function andWhere($column, $value, $operator = self::EQUALS, $quote = true) {

        return $this->criteria($this->where, $column, $value, $operator, self::LOGICAL_AND, $quote);
    }

    /**
     * Add an OR WHERE condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function orWhere($column, $value, $operator = self::EQUALS, $quote = true) {
        return $this->orCriteria($this->where, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function whereIn($column, array $values, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaIn($this->where, $column, $values, $connector, $quote);
    }

    /**
     * Add a NOT IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function whereNotIn($column, array $values, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaNotIn($this->where, $column, $values, $connector, $quote);
    }

    /**
     * Add a BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function whereBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaBetween($this->where, $column, $min, $max, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN WHERE condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function whereNotBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaNotBetween($this->where, $column, $min, $max, $connector, $quote);
    }


    /**
     * Get the WHERE portion of the statement as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'WHERE' text, default true
     * @return string WHERE portion of the statement
     */
    protected function getWhereString($usePlaceholders = true, $includeText = true) {
        $statement = $this->getCriteriaString($this->where, $usePlaceholders, $this->wherePlaceholderValues);

        if ($includeText && $statement) {
            $statement = "WHERE " . $statement;
        }

        return $statement;
    }

    /**
     * Get the WHERE placeholder values.
     *
     * @return array WHERE placeholder values
     */
    protected function getWherePlaceholderValues() {
        return $this->wherePlaceholderValues;
    }

    /**
     * Add a GROUP BY column.
     *
     * @param  string $column column name
     * @param  string|null $order optional order direction, default none
     * @return Adapter
     */
    protected function groupBy($column, $order = null) {

        $this->groupBy[] = array('column' => $column,
            'order'  => $order);

        return $this;
    }



    /**
     * Get the GROUP BY portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'GROUP BY' text, default true
     * @return string GROUP BY portion of the statement
     */
    protected function getGroupByString($includeText = true) {
        $statement = "";

        foreach ($this->groupBy as $groupBy) {
            if(!strstr($groupBy['column'],'.'))
                $groupBy['column'] = $this->getColumnPrefix().$groupBy['column'];
            $statement .= $groupBy['column'];

            if ($groupBy['order']) {
                $statement .= " " . $groupBy['order'];
            }

            $statement .= ", ";
        }

        $statement = substr($statement, 0, -2);

        if ($includeText && $statement) {
            $statement = "GROUP BY " . $statement;
        }

        return $statement;
    }

    /**
     * Add an open bracket for nesting HAVING conditions.
     *
     * @param  string $connector optional logical connector, default AND
     * @return Adapter
     */
    protected function openHaving($connector = self::LOGICAL_AND) {
        return $this->openCriteria($this->having, $connector);
    }

    /**
     * Add a closing bracket for nesting HAVING conditions.
     *
     * @return Adapter
     */
    protected function closeHaving() {
        return $this->closeCriteria($this->having);
    }

    /**
     * Add a HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function having($column, $value, $operator = self::EQUALS, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteria($this->having, $column, $value, $operator, $connector, $quote);
    }

    /**
     * Add an AND HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function andHaving($column, $value, $operator = self::EQUALS, $quote = true) {
        return $this->criteria($this->having, $column, $value, $operator, self::LOGICAL_AND, $quote);
    }

    /**
     * Add an OR HAVING condition.
     *
     * @param  string $column colum name
     * @param  mixed $value value
     * @param  string $operator optional comparison operator, default =
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function orHaving($column, $value, $operator = self::EQUALS, $quote = true) {
        return $this->orCriteria($this->having, $column, $value, $operator, self::LOGICAL_OR, $quote);
    }

    /**
     * Add an IN WHERE condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function havingIn($column, array $values, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaIn($this->having, $column, $values, $connector, $quote);
    }

    /**
     * Add a NOT IN HAVING condition.
     *
     * @param  string $column column name
     * @param  array $values values
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function havingNotIn($column, array $values, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaNotIn($this->having, $column, $values, $connector, $quote);
    }

    /**
     * Add a BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function havingBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaBetween($this->having, $column, $min, $max, $connector, $quote);
    }

    /**
     * Add a NOT BETWEEN HAVING condition.
     *
     * @param  string $column column name
     * @param  mixed $min minimum value
     * @param  mixed $max maximum value
     * @param  string $connector optional logical connector, default AND
     * @param  bool|null $quote optional auto-escape value, default to global
     * @return Adapter
     */
    protected function havingNotBetween($column, $min, $max, $connector = self::LOGICAL_AND, $quote = true) {
        return $this->criteriaNotBetween($this->having, $column, $min, $max, $connector, $quote);
    }



    /**
     * Get the HAVING portion of the statement as a string.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @param  bool $includeText optional include 'HAVING' text, default true
     * @return string HAVING portion of the statement
     */
    protected function getHavingString($usePlaceholders = true, $includeText = true) {
        $statement = $this->getCriteriaString($this->having, $usePlaceholders, $this->havingPlaceholderValues);

        if ($includeText && $statement) {
            $statement = "HAVING " . $statement;
        }

        return $statement;
    }

    /**
     * Get the HAVING placeholder values.
     *
     * @return array HAVING placeholder values
     */
    protected function getHavingPlaceholderValues() {
        return $this->havingPlaceholderValues;
    }

    /**
     * Add a column to ORDER BY.
     *
     * @param  string $column column name
     * @param  string $order optional order direction, default ASC
     * @return Adapter
     */
    protected function orderBy($column, $order = self::ORDER_BY_ASC) {

        $this->orderBy[] = array('column' => $column,
            'order'  => $order);

        return $this;
    }


    /**
     * Get the ORDER BY portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'ORDER BY' text, default true
     * @return string ORDER BY portion of the statement
     */
    protected function getOrderByString($includeText = true) {
        $statement = "";

        foreach ($this->orderBy as $orderBy) {
            if(!strstr($orderBy['column'],'.'))
                $orderBy['column'] = $this->getColumnPrefix().$orderBy['column'];
            $statement .= $orderBy['column'] . " " . $orderBy['order'] . ", ";
        }

        $statement = substr($statement, 0, -2);

        if ($includeText && $statement) {
            $statement = "ORDER BY " . $statement;
        }

        return $statement;
    }

    /**
     * Set the LIMIT on number of rows to return with optional offset.
     *
     * @param  int|string $limit number of rows to return
     * @param  int|string $offset optional row number to start at, default 0
     * @return Adapter
     */
    protected function limit($limit, $offset = 0) {
        $this->limit['limit'] = $limit;
        $this->limit['offset'] = $offset;

        return $this;
    }

    protected function page($page_number=1,$page_size = 10) {

        $this->limit['limit'] = $page_size;
        $this->limit['offset'] = ($page_number-1)*$page_size;
        return $this;
    }

    /**
     * Get the LIMIT on number of rows to return.
     *
     * @return int|string LIMIT on number of rows to return
     */
    protected function getLimit() {
        return $this->limit['limit'];
    }

    /**
     * Get the LIMIT row number to start at.
     *
     * @return int|string LIMIT row number to start at
     */
    protected function getLimitOffset() {
        return $this->limit['offset'];
    }

    /**
     * Get the LIMIT portion of the statement as a string.
     *
     * @param  bool $includeText optional include 'LIMIT' text, default true
     * @return string LIMIT portion of the statement
     */
    protected function getLimitString($includeText = true) {
        $statement = "";

        if (!$this->limit) {
            return $statement;
        }

        $statement .= $this->limit['limit'];

        if ($this->limit['offset'] !== 0) {
            $statement .= " OFFSET " . $this->limit['offset'];
        }

        if ($includeText && $statement) {
            $statement = "LIMIT " . $statement;
        }

        return $statement;
    }

    /**
     * Whether this is a SELECT statement.
     *
     * @return bool whether this is a SELECT statement
     */
    protected function isSelect() {
        return !empty($this->select);
    }

    /**
     * Whether this is an INSERT statement.
     *
     * @return bool whether this is an INSERT statement
     */
    protected function isInsert() {
        return !empty($this->insert);
    }

    /**
     * Whether this is a REPLACE statement.
     *
     * @return bool whether this is a REPLACE statement
     */
    protected function isReplace() {
        return !empty($this->replace);
    }

    /**
     * Whether this is an UPDATE statement.
     *
     * @return bool whether this is an UPDATE statement
     */
    protected function isUpdate() {
        return !empty($this->update);
    }

    /**
     * Whether this is a DELETE statement.
     *
     * @return bool whether this is a DELETE statement
     */
    protected function isDelete() {
        return !empty($this->delete);
    }


    protected function getSelectFilterString($usePlaceholders = true){
        $statement = "";

        if ($this->from) {
            $statement .= " " . $this->getFromString();
        }

        if ($this->where) {
            $statement .= " " . $this->getWhereString($usePlaceholders);
        }

        if ($this->groupBy) {
            $statement .= " " . $this->getGroupByString();
        }

        if ($this->having) {
            $statement .= " " . $this->getHavingString($usePlaceholders);
        }

        if ($this->orderBy) {
            $statement .= " " . $this->getOrderByString();
        }

        if ($this->limit) {
            $statement .= " " . $this->getLimitString();
        }

        return $statement;
    }
    /**
     * Get the full SELECT statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full SELECT statement
     */
    protected function  getSelectStatement($usePlaceholders = true) {
        $statement = "";

        if (!$this->isSelect()) {
            return $statement;
        }
        $statement .= $this->getSelectString();
        $statement .= $this->getSelectFilterString($usePlaceholders);
        return $statement;
    }

    /**
     * Get the full INSERT statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full INSERT statement
     */
    protected function getInsertStatement($usePlaceholders = true) {
        $statement = "";

        if (!$this->isInsert()) {
            return $statement;
        }

        $statement .= $this->getInsertString();

        if ($this->set) {
            $statement .= " " . $this->getSetString($usePlaceholders);
        }

        return $statement;
    }

    /**
     * Get the full REPLACE statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full REPLACE statement
     */
    protected function getReplaceStatement($usePlaceholders = true) {
        $statement = "";

        if (!$this->isReplace()) {
            return $statement;
        }

        $statement .= $this->getReplaceString();

        if ($this->set) {
            $statement .= " " . $this->getSetString($usePlaceholders);
        }

        return $statement;
    }

    /**
     * Get the full UPDATE statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full UPDATE statement
     */
    protected function getUpdateStatement($usePlaceholders = true) {
        $statement = "";

        if (!$this->isUpdate()) {
            return $statement;
        }

        $statement .= $this->getUpdateString();

        if ($this->set) {
            $statement .= " " . $this->getSetString($usePlaceholders);
        }

        if ($this->where) {
            $statement .= " " . $this->getWhereString($usePlaceholders);
        }

        // ORDER BY and LIMIT are only applicable when updating a single table.
        if (!$this->join) {
            if ($this->orderBy) {
                $statement .= " " . $this->getOrderByString();
            }

            if ($this->limit) {
                $statement .= " " . $this->getLimitString();
            }
        }

        return $statement;
    }

    /**
     * Get the full DELETE statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full DELETE statement
     */
    protected function getDeleteStatement($usePlaceholders = true) {
        $statement = "";

        if (!$this->isDelete()) {
            return $statement;
        }

        $statement .= $this->getDeleteString();

        if ($this->from) {
            $statement .= " " . $this->getFromString();
        }

        if ($this->where) {
            $statement .= " " . $this->getWhereString($usePlaceholders);
        }

        // ORDER BY and LIMIT are only applicable when deleting from a single
        // table.
        if ($this->isDeleteTableFrom()) {
            if ($this->orderBy) {
                $statement .= " " . $this->getOrderByString();
            }

            if ($this->limit) {
                $statement .= " " . $this->getLimitString();
            }
        }

        return $statement;
    }

    /**
     * Get the full SQL statement.
     *
     * @param  bool $usePlaceholders optional use ? placeholders, default true
     * @return string full SQL statement
     */
    protected function getStatement($usePlaceholders = true) {
        $statement = "";

        if ($this->isSelect()) {
            $statement = $this->getSelectStatement($usePlaceholders);
        }
        elseif ($this->isInsert()) {
            $statement = $this->getInsertStatement($usePlaceholders);
        }
        elseif ($this->isReplace()) {
            $statement = $this->getReplaceStatement($usePlaceholders);
        }
        elseif ($this->isUpdate()) {
            $statement = $this->getUpdateStatement($usePlaceholders);
        }
        elseif ($this->isDelete()) {
            $statement = $this->getDeleteStatement($usePlaceholders);
        }

        return $statement;
    }

    /**
     * Get all placeholder values (SET, WHERE, and HAVING).
     *
     * @return array all placeholder values
     */
    protected function getPlaceholderValues() {
        return array_merge($this->getSetPlaceholderValues(),
            $this->getWherePlaceholderValues(),
            $this->getHavingPlaceholderValues());
    }

    /**
     * Execute the statement using the PDO database connection.
     *
     * @return PDOStatement|false executed statement or false if failed
     */
//    protected function execute() {
//        $PdoConnection = $this->getPdoConnection();
//
//        // Without a PDO database connection, the statement cannot be executed.
//        if (!$PdoConnection) {
//            return false;
//        }
//
//        $statement = $this->getStatement();
//
//        // Only execute if a statement is set.
//        if ($statement) {
//            $PdoStatement = $PdoConnection->prepare($statement);
//            $PdoStatement->execute($this->getPlaceholderValues());
//
//            return $PdoStatement;
//        }
//        else {
//            return false;
//        }
//    }
    /**
     * Get the full SQL statement without value placeholders.
     *
     * @return string full SQL statement
     */
    public function __toString() {
        return $this->getStatement(false);
    }

    /**
     * 初始化，select的值，参数$where可以指定初始化哪一项
     * @param $what
     */
    protected function initSqlParams($what='')
    {
        if($what==''){
            $this->option = [];
            $this->select = [];
            $this->delete = [];
            $this->set = [];
            $this->from = [];
            $this->join = [];
            $this->where = [];
            $this->groupBy = [];
            $this->having = [];
            $this->orderBy = [];
            $this->limit = [];
            $this->setPlaceholderValues = [];
            $this->wherePlaceholderValues = [];
            $this->havingPlaceholderValues = [];
        }else
            $this->$what=[];
    }
}