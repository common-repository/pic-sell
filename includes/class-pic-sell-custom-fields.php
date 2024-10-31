<?php
/**
 * CLASS custom fields
 * @author Benjamin C
 * @version 1.0.0
 */

 class Pic_Sell_Custom_Fields{

    protected $type;
    protected $section_id;
    protected $section_title;
    protected $callback;

    public function __construct($type, $section_id, $title_section, $callback)
    {

        $this->type = $type;   
        $this->section_id = $section_id; 
        $this->callback = $callback;
        $this->section_title = $title_section;

        add_action( 'add_meta_boxes_'.$type, array($this, 'add_meta_box') );

    }

    public function add_meta_box($post)
    {

        add_meta_box(
            $this->get_type() . '_' . $this->get_section_id(),
            $this->get_section_title(),
            $this->get_callback(),
            $this->get_type(),
            'advanced',
            'high'
        );
  
    }


    public function get_type(){
        return $this->type;
    }

    public function get_section_id(){
        return $this->section_id;
    }

    public function get_callback(){
        return $this->callback;
    }

    public function get_section_title(){
        return $this->section_title;
    }
 }