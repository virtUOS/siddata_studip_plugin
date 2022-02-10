<?php

/**
 * Class SiddataQuestion
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 */
class SiddataQuestion extends SiddataActivityComponent {

    private $question_text;
    private $answer_type;
    private $selection_answers;

    /**
     * SiddataQuestion constructor.
     * @param string $id
     * @param string $question_text
     * @param string $answer_type
     * @param array $selection_answers
     */
    public function __construct($id, $question_text, $answer_type, $selection_answers=[]) {
        $this->id = $id;
        $this->question_text = $question_text;
        $this->answer_type = $answer_type;
        $this->selection_answers = $selection_answers;
    }

    /**
     * @return string
     */
    public function getText() { return $this->question_text; }

    /**
     * @return string
     */
    public function getType() { return $this->answer_type; }

    /**
     * @return array
     */
    public function getSelectionAnswers() {
        return $this->selection_answers;
    }
}
