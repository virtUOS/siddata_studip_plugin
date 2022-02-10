<?php


/**
 * Class SiddataAnswerSearch
 *
 * This class makes an auto completion in activity answer fields possible.
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataAnswerSearch extends SearchType
{
    /**
     * @var array
     */
    private $answers;

    /**
     * SiddataAnswerSearch constructor.
     * @param array $answers
     */
    public function __construct($answers=array())
    {
        $this->answers = $answers;
    }

    /**
     * Returns the results to a given keyword. To get the results is the
     * job of this routine and it does not even need to come from a database.
     * The results should be an array in the form
     * array (
     *   array($key, $name),
     *   array($key, $name),
     *   ...
     * )
     * where $key is an identifier like user_id and $name is a displayed text
     * that should appear to represent that ID.
     *
     * @param string $keyword
     * @param array $contextual_data
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     *
     * @return array
     */
    public function getResults($keyword, $contextual_data = array(), $limit = PHP_INT_MAX, $offset = 0)
    {
        $results = array();
        foreach ($this->answers as $key => $answer) {
            if (strpos(strtolower($answer), strtolower($keyword)) !== false) {
                $results[$key] = [$key, $answer];
            }
        }
        return $results;
    }

    /**
     * Returns the path to this file, so that this class can be autoloaded and is
     * always available when necessary.
     * Should be: "return __file__;"
     *
     * @return string path to this file
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}