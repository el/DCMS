<?php

/**
 * Sayfalar Model
 */
class csayfalar extends Model {

	/**
	 * Uniq id
	 * type: number
	 */
	public	$id;
	/**
	 * Content ID
	 * type: number
	 */
	public	$cid;
	/**
	 * Content Creator
	 * type: text
	 */
	public	$user;
	/**
	 * Content creation time
	 * type: datetime
	 */
	public	$cdate;
	/**
	 * Content flag 
	 * options: 0 (deleted), 3(active)
	 * type: number
	 */
	public	$flag;
	/**
	 * Languages: 
	 * options: 0 (tr:Türkçe), 1 (en:English), 2 (de:Almanca), 3 (sp:İspanyolca), 4 (fr:Fransızca), 
	 * type: number
	 */
	public	$language;
	/**
	 * App ID
	 * type: number
	 */
	public	$app;
	/**
	 * Sort number smallest first (ASC)
	 * type: number
	 */
	public	$sort;
	/**
	 * Parent ID
	 * type: number
	 */
	public	$up;
	/**
	 * Hit number
	 * type: number
	 */
	public	$hit;
	/**
	 * Page url
	 * type: text
	 */
	public	$page_url;
	/**
	 * Page keywords
	 * type: text
	 */
	public	$page_keywords;
	/**
	 * Page description
	 * type: text
	 */
	public	$page_description;
	/**
	 * Adı
	 * type: text
	 */
	public	$iname;
	/**
	 * İçeriği
	 * type: content
	 */
	public	$iicerigi;
	/**
	 * Connected fields (foreign keys)
	 * type: array
	 */
	public	$bounds = array( );

	function __construct ($v=array()) {
		foreach($v as $k => $f)
			if (property_exists($this,$k)){
				$a = "set_$k";
				$this->$a($f);
			}
	}

	/*******************************************
	 * Getters
	*******************************************/
	public function get_id () {
		return $this->id;
	}

	public function get_cid () {
		return $this->cid;
	}

	public function get_user () {
		return $this->user;
	}

	public function get_cdate () {
		return $this->cdate;
	}

	public function get_flag () {
		return $this->flag;
	}

	public function get_language () {
		return $this->language;
	}

	public function get_app () {
		return $this->app;
	}

	public function get_sort () {
		return $this->sort;
	}

	public function get_up () {
		return $this->up;
	}

	public function get_hit () {
		return $this->hit;
	}

	public function get_page_url () {
		return $this->page_url;
	}

	public function get_page_keywords () {
		return $this->page_keywords;
	}

	public function get_page_description () {
		return $this->page_description;
	}

	public function get_iname () {
		return $this->iname;
	}

	public function get_iicerigi () {
		return $this->iicerigi;
	}



	/*******************************************
	 * Setters
	*******************************************/
	public function set_id ($val) {
		$val = intval($val);
		$this->id = $val;
		return $this;
	}

	public function set_cid ($val) {
		$val = intval($val);
		$this->cid = $val;
		return $this;
	}

	public function set_user ($val) {
		$this->user = $val;
		return $this;
	}

	public function set_cdate ($val) {
		$this->cdate = $val;
		return $this;
	}

	public function set_flag ($val) {
		$val = intval($val);
		$this->flag = $val;
		return $this;
	}

	public function set_language ($val) {
		$val = intval($val);
		$this->language = $val;
		return $this;
	}

	public function set_app ($val) {
		$val = intval($val);
		$this->app = $val;
		return $this;
	}

	public function set_sort ($val) {
		$val = intval($val);
		$this->sort = $val;
		return $this;
	}

	public function set_up ($val) {
		$val = intval($val);
		$this->up = $val;
		return $this;
	}

	public function set_hit ($val) {
		$val = intval($val);
		$this->hit = $val;
		return $this;
	}

	public function set_page_url ($val) {
		$this->page_url = $val;
		return $this;
	}

	public function set_page_keywords ($val) {
		$this->page_keywords = $val;
		return $this;
	}

	public function set_page_description ($val) {
		$this->page_description = $val;
		return $this;
	}

	public function set_iname ($val) {
		$this->iname = $val;
		return $this;
	}

	public function set_iicerigi ($val) {
		$this->iicerigi = $val;
		return $this;
	}

}
