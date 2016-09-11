<?php

/**
 * Sayfalar Controller
 */
class csayfalarController extends Controller {

	/**
	 * Giriş (index) action
	 */
	public function indexAction(){
		/*
		$id = $this->getID();
		$query = 'SELECT * FROM `csayfalar` WHERE cid = ? AND '.$this->active;
		$sth = $this->dbh->prepare($query);
		$sth->execute(array($id));
		try {
			$sth = $sth->fetch();
		} catch (PDOException $e) {
			return;
		}
		$model = csayfalar($sth);
		$model = $this->getFromCID('csayfalar',$id);
		*/

		$lang = $this->language["short"];
		return $this->render('csayfalar.html.twig',array('model' => ''));
	}

	/**
	 * Sayfa (page) action
	 */
	public function pageAction () {
		return $this->render('csayfalar_page.html.twig',array());	}

}