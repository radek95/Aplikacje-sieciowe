<?php
require_once dirname(__FILE__).'/../config.php';

// KONTROLER strony kalkulatora

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

//ochrona kontrolera - poniższy skrypt przerwie przetwarzanie w tym punkcie gdy użytkownik jest niezalogowany
include _ROOT_PATH.'/app/security/check.php';

//pobranie parametrów
function getParams(&$x,&$y,&$operation){
	$kwota = isset($_REQUEST['kwota']) ? $_REQUEST['kwota'] : null;
	$liczba_lat = isset($_REQUEST['liczba_lat']) ? $_REQUEST['liczba_lat'] : null;
	$wysokosc_oprocentowania = isset($_REQUEST['wysokosc_oprocentowania']) ? $_REQUEST['wysokosc_oprocentowania'] : null;	
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,&$messages){
	// sprawdzenie, czy parametry zostały przekazane
	if ( ! (isset($kwota) && isset($liczba_lat) && isset($wysokosc_oprocentowania))) {
		// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
		// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
		return false;
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
	if (count ( $messages ) != 0) return false;
	
	// sprawdzenie, czy kwota i liczba lat są liczbami całkowitymi
	if (! is_numeric( $kwota )) {
		$messages [] = 'Kwota nie jest liczbą całkowitą';
	}
	
	if (! is_numeric( $liczba_lat )) {
		$messages [] = 'Liczba lat nie jest liczbą całkowitą';
	}	

	if (count ( $messages ) != 0) return false;
	else return true;
}

function process(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,&$messages,&$result){
	global $role;
	
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

//definicja zmiennych kontrolera
$kwota = null;
$liczba_lat = null;
$wysokosc_oprocentowania = null;
$result = null;
$messages = array();

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($x,$y,$operation);
if ( validate(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,$messages) { // gdy brak błędów
	process(&$kwota,&$liczba_lat,&$wysokosc_oprocentowania,$messages,$result);
}

// Wywołanie widoku z przekazaniem zmiennych
// - zainicjowane zmienne ($messages,$x,$y,$operation,$result)
//   będą dostępne w dołączonym skrypcie
include 'calc_view.php';