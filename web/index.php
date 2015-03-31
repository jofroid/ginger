<?php
// Include all dependencies
require_once '../vendor/autoload.php';

// Include model
Propel::init("../build/conf/ginger-conf.php");
set_include_path("../build/classes" . PATH_SEPARATOR . get_include_path());

// Include config
require_once '../config.php';

require_once '../lib/Koala/Koala.class.php';
require_once '../class/Ginger.class.php';

class MyAuth extends \Koala\KoalaAuth {
	public $ginger;
	public function auth($app) {
		parent::auth($app);
		$this->ginger = new Ginger($app->request()->params('key'));
	}
}
$myAuth = new MyAuth();

$app = new \Koala\Koala($myAuth, array(
    'debug' => false,
    'templates.path' => '../templates'
));




/***********************************************************************
 *                             Routes
 ***********************************************************************/
// récupération des stats
$app->get('/v1/stats', function () use ($app, $myAuth) {
	$r = $myAuth->ginger->getStats();
	$app->render('success.json.php', array('result'=>$r));
});

// récupération d'un utilisateur
$app->get('/v1/:login', function ($login) use ($app, $myAuth) {
	$r = $myAuth->ginger->getPersonneDetails($login);
	$app->render('success.json.php', array('result'=>$r));
});

// récupération d'un utilisateur par badge
$app->get('/v1/badge/:card', function ($card) use ($app, $myAuth) {
	$r = $myAuth->ginger->getPersonneDetailsByCard($card);
	$app->render('success.json.php', array('result'=>$r));
});

// récupération des cotisations
$app->get('/v1/:login/cotisations', function ($login) use ($app, $myAuth) {
	$r = $myAuth->ginger->getPersonneCotisations($login);
	$app->render('success.json.php', array('result'=>$r));
});

// recherche d'une personne
$app->get('/v1/find/:loginpart', function ($loginpart) use ($app, $myAuth) {
	$r = $myAuth->ginger->findPersonne($loginpart);
	$app->render('success.json.php', array('result'=>$r));
});

// ajout d'une cotisation
$app->post('/v1/:login/cotisations', function ($login) use ($app, $myAuth) {
	$debut = $app->request()->params('debut');
	$fin = $app->request()->params('fin');
	$montant = $app->request()->params('montant');
	if (empty($debut) or empty($fin) or empty($montant))
		throw new \Koala\ApiException(400);
	
	$r = $myAuth->ginger->addCotisation($login, strtotime($debut), strtotime($fin), $montant);
	$app->render('success.json.php', array('result'=>$r));
});

// suppression d'une cotisation
$app->delete('/v1/cotisations/:cotisation', function ($cotisation) use ($app, $myAuth) {
	$r = $myAuth->ginger->deleteCotisation($cotisation);
	$app->render('success.json.php', array('result'=>$r));
});

// récupération des exterieurs
$app->get('/v1/exts', function () use ($app, $myAuth) {
	$r = $myAuth->ginger->getExts();
	$app->render('success.json.php', array('result'=>$r));
});

// set d'une personne
$app->post('/v1/:login/edit', function ($login) use ($app, $myAuth) {
	$nom = $app->request()->params('nom');
	$prenom = $app->request()->params('prenom');
	$mail = $app->request()->params('mail');
	$is_adulte = $app->request()->params('is_adulte');
	if (empty($nom) or empty($prenom) or empty($mail) or empty($is_adulte) )
		throw new \Koala\ApiException(400);
	
	$r = $myAuth->ginger->setPersonne($login, $nom, $prenom, $mail, $is_adulte);
	$app->render('success.json.php', array('result'=>$r));
});

/***********************************************************************
 *                             Launch
 ***********************************************************************/
$app->run();

?>
