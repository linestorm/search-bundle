<?php

namespace LineStorm\SearchBundle\Search\Exception;

use Exception;

/**
 * Class FullTextIndexMissingException
 *
 * @package LineStorm\SearchBundle\Search\Exception
 */
class FullTextIndexMissingException extends \Exception
{
    private $table;
    private $index;
    private $columns;

    /**
     * @param string    $table
     * @param int       $index
     * @param array     $columns
     * @param Exception $previous
     */
    public function __construct($table, $index, array $columns, Exception $previous = null)
    {
        $this->table   = $table;
        $this->index   = $index;
        $this->columns = $columns;

        parent::__construct("The table '{$table}' does not have a full index.\nTry running \"{$this->getCommand()}\"", 0, $previous);
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        $columns = implode(',', $this->columns);

        return "CREATE FULLTEXT INDEX {$this->index} ON {$this->table}({$columns})";
    }

}
