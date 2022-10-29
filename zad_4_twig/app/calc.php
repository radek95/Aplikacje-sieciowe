<?php
// KONTROLER strony kalkulatora
require_once dirname(__FILE__).'/../config.php';

//załaduj Twig
require_once _ROOT_PATH.'/lib/Twig/Autoloader.php';

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

// 1. pobranie parametrów

$kwota = $_REQUEST ['kwota'];
$liczba_lat = $_REQUEST ['liczba lat'];
$wysokosc_oprocentowania = $_REQUEST ['wysokosc oprocentowania'];

// 2. walidacja parametrów z przygotowaniem zmiennych dla widoku

//domyślnie pokazuj wstęp strony (tytuł i tło)
$hide_intro = false;

// sprawdzenie, czy parametry zostały przekazane - jeśli nie to wyświetl widok bez obliczeń
if (!(isset($kwota) && isset($liczba lat) && isset($wysokosc oprocentowania))) {
	//sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
	$messages [] = 'Błędne wywołanie aplikacji. Brak jednego z parametrów.';
}

// sprawdzenie, czy potrzebne wartości zostały przekazane
if ( $kwota == "") {
	$messages [] = 'Nie podano kwoty';
}
if ( $liczba_lat == "") {
	$messages [] = 'Nie podano liczby lat';
}
if ( $wysokosc_oprocentowania == "") {
	$messages [] = 'Nie podano wysokosci oprocentowania';
}

//nie ma sensu walidować dalej gdy brak parametrów
if (empty( $messages )) {
	
	// sprawdzenie, czy kwota i $liczba lat są liczbami całkowitymi
	if (! is_numeric( $kwota )) {
		$messages [] = 'Kwota nie jest liczbą całkowitą';
	}
	
	if (! is_numeric( $liczba_lat )) {
		$messages [] = 'Liczba lat nie jest liczbą całkowitą';
	}	
}
	
	// 3. wykonaj zadanie jeśli wszystko w porządku
	
if (count ( $messages ) == 0) { // gdy brak błędów
		
	$infos [] = 'Parametry poprawne. Wykonuję obliczenia.';
		
	//konwersja parametrów na int
	$kwota = intval($kwota);
	$liczba_lat = intval($liczba_lat);

	//wykonanie operacji
	if ($role == 'admin' && $kwota>10 000){
		$messages [] = 'Tylko administrator może obliczac kwote raty dla tak duzej kwoty. Podaj nizsza kwote.';
	} else {
		$result = ($kwota*$wysokosc_oprocentowania)/(12*(1-(12/(12+$wysokosc_oprocentowania))^$liczba_lat));
	}
}

// 4. Przygotowanie szablonu i zmiennych

//start Twig
Twig_Autoloader::register();
//załaduj szablony (wskazanie folderów z potrzebnymi szablonami)
$loader = new Twig_Loader_Filesystem(_ROOT_PATH.'/templates'); //szablon ogólny
$loader->addPath(_ROOT_PATH.'/app'); //szablon strony kalkulatora
//skonfiguruj folder cache
$twig = new Twig_Environment($loader, array(
    'cache' => _ROOT_PATH.'/twig_cache',
));

//definicja zmiennych kontrolera
$kwota = null;
$liczba_lat = null;
$wysokosc_oprocentowania = null;
$result = null;
$messages = array();

//przygotowanie zmiennych dla szablonu
$variables = array(
	'app_url' => _APP_URL,
	'root_path' => _ROOT_PATH,
	'page_title' => 'Cwiczenie 4 - szablon Twig',
	'page_description' => 'Profesjonalne szablonowanie oparte na bibliotece Twig',
	'page_header' => 'Szablony Twig',
	'hide_intro' => $hide_intro
);
if (isset($kwota)) $variables ['kwota'] =  $kwota;
if (isset($liczba_lat)) $variables ['liczba lat'] = $liczba_lat;
if (isset($wysokosc_oprocentowania)) $variables ['wysokosc oprocentowania'] = $wysokosc_oprocentowania;
if (isset($result)) $variables ['result'] = $result;
if (isset($messages)) $variables ['messages'] = $messages;
if (isset($infos)) $variables ['infos'] = $infos;

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($x,$y,$operation);
if ( validate(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,$messages) { // gdy brak błędów
	process(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,$messages,$result);
}

// 5. Wywołanie szablonu (wygenerowanie widoku)
echo $twig->render('calc.html', $variables);