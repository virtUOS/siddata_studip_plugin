<?php

/**
 * Class SiddataPerson
 *
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 */
class SiddataPerson extends SiddataActivityComponent {

    private $image;
    private $first_name;
    private $surname;
    private $title;
    private $email;
    private $description;
    private $recommendation_reason;
    private $url;
    private $editable;

    /**
     * SiddataPerson constructor.
     * @param string $id
     * @param string $image
     * @param string $first_name
     * @param string $surname
     * @param string $email
     * @param string $description
     * @param string $url
     * @param string $recommendation_reason
     * @param boolean $editable
     *
     */
    public function __construct($id, $image, $first_name, $surname, $title, $email, $description, $url='', $recommendation_reason='', $editable = false) {

        if(!isset($first_name) and !isset($surname)) {
            $this->first_name = "Unbekannt";
            $surname = "";
        }

        $this->id = $id;
        $this->image = $image;
        $this->first_name = $first_name;
        $this->surname = $surname;
        $this->title = $title;
        $this->email = $email;
        $this->description = $description;
        $this->url = $url;
        $this->recommendation_reason = $recommendation_reason;
        $this->editable = $editable;

    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getImage() { return $this->image; }

    /**
     * @return string
     */
    public function getName() { return $this->title . " " . $this->first_name . " " . $this->surname; }

    /**
     * @return string
     */
    public function getEmail() { return $this->email; }

    /**
     * Get description
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Get description as plain text
     * @return string
     */
    public function getRawDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getRecommendationReason() { return $this->recommendation_reason; }

    /**
     * @return string
     */
    public function getURL() { return $this->url; }

    /**
     * @return boolean
     */
    public function isEditable() { return $this->editable; }


}
