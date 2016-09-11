<?php

/**
 * Anasayfa Controller
 */
class homeController extends Controller {

	/**
	 * Giriş (index) action
	 */
	public function indexAction(){
		/*
		$id = $this->getID();
		$query = 'SELECT * FROM `home` WHERE cid = ? AND '.$this->active;
		$sth = $this->dbh->prepare($query);
		$sth->execute(array($id));
		try {
			$sth = $sth->fetch();
		} catch (PDOException $e) {
			return;
		}
		$model = home($sth);
		$model = $this->getFromCID('home',$id);
		*/
		return $this->render('home.html.twig',array('model' => ''));
	}

}