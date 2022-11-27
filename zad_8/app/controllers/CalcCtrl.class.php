<?php
// W skrypcie definicji kontrolera nie trzeba dołączać już niczego.
// Kontroler wskazuje tylko za pomocą 'use' te klasy z których jawnie korzysta
// (gdy korzysta niejawnie to nie musi - np używa obiektu zwracanego przez funkcję)

// Zarejestrowany autoloader klas załaduje odpowiedni plik automatycznie w momencie, gdy skrypt będzie go chciał użyć.
// Jeśli nie wskaże się klasy za pomocą 'use', to PHP będzie zakładać, iż klasa znajduje się w bieżącej
// przestrzeni nazw - tutaj jest to przestrzeń 'app\controllers'.

// Przypominam, że tu również są dostępne globalne funkcje pomocnicze - o to nam właściwie chodziło

namespace app\controllers;

//zamieniamy zatem 'require' na 'use' wskazując jedynie przestrzeń nazw, w której znajduje się klasa
use app\forms\PersonEditForm;
use DateTime;
use PDOException;
use app\forms\CalcForm;
use app\transfer\CalcResult;

class CalcCtrl {

	private $form;   //dane formularza (do obliczeń i dla widoku)
	private $result; //inne dane dla widoku

	/** 
	 * Konstruktor - inicjalizacja właściwości
	 */
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new CalcForm();
		$this->result = new CalcResult();
	}
	
	/** 
	 * Pobranie parametrów
	 */
	public function getParams(){
		$this->form->kwota = getFromRequest('kwota',true,'Błędne wywołanie aplikacji');
		$this->form->liczba_lat = getFromRequest('liczba lat',true,'Błędne wywołanie aplikacji');
		$this->form->wysokosc_oprocentowania = getFromRequest('wysokosc oprocentowania',true,'Błędne wywołanie aplikacji');
	}
	
	/** 
	 * Walidacja parametrów
	 * @return true jeśli brak błedów, false w przeciwnym wypadku 
	 */
	public function validate() {
		// sprawdzenie, czy parametry zostały przekazane
		if (! (isset ( $this->form->kwota ) && isset ( $this->form->liczba_lat ) && isset ( $this->form->wysokosc_oprocentowania ))) {
			// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
			return false;
		}
		
		// sprawdzenie, czy potrzebne wartości zostały przekazane
		
		if (empty(trim($this->form->kwota))) {
			$this->msgs->addError('Wprowadź kwotę');
		}
		if (empty(trim($this->form->liczba_lat))) {
			$this->msgs->addError('Wprowadź liczbę lat');
		}
		if (empty(trim($this->form->wysokosc_oprocentowania))) {
			$this->msgs->addError('Wprowadź wysokość oprocentowania');
		}
		
		// nie ma sensu walidować dalej gdy brak parametrów
		if (! getMessages()->isError()) {
			
			// sprawdzenie, czy koszt i liczba lat są liczbami całkowitymi i czy wysokosc oprocentowania jest liczba rzeczywista 
			if (! is_numeric ( $this->form->kwota )) {
				$this->msgs->addError('Kwota nie jest liczbą całkowitą');
			}
			
			if (! is_numeric ( $this->form->liczba_lat )) {
				$this->msgs->addError('Liczba lat nie jest liczbą całkowitą');
			}
			if (! is_double ( $this->form->wysokosc_oprocentowania )) {
				$this->msgs->addError('Wysokosc oprocentowania nie jest liczbą rzeczywista');
			}
		}
		
		return ! getMessages()->isError();
	}
	
	/** 
	 * Pobranie wartości, walidacja, obliczenie i wyświetlenie
	 */
	public function action_calcCompute(){

		$this->getParams();
		
		if ($this->validate()) {
			// 2. Zapis danych w bazie
			try {
				//2.1 Nowy rekord
				if (inRole('admin') && $kwota>10 000){
					getMessages()->addError('Tylko administrator może obliczac kwote raty dla tak duzej kwoty. Podaj nizsza kwote.');
				}else{
					if ($this->form->id == '') {
						getDB()->insert("recordID", [
							"kwota" => $this->form->kwota,
							"liczba lat" => $this->form->liczba_lat,
							"wysokosc oprocentowania" => $this->form->wysokosc_oprocentowania
							//wykonanie operacji
							"result" = ($this->form->kwota * $this->form->wysokosc_oprocentowania)/(12*(1-(12/(12 + $this->form->wysokosc_oprocentowania))^$this->form->liczba_lat));
						]);
						getMessages()->addInfo('Wykonano obliczenia.');
					}else{
					//2.2 Edycja rekordu o danym ID
						getDB()->update("recordID", [
							"kwota" => $this->form->kwota,
							"liczba lat" => $this->form->liczba_lat,
							"wysokosc oprocentowania" => $this->form->wysokosc_oprocentowania
							//wykonanie operacji
							"result" = ($this->form->kwota * $this->form->wysokosc_oprocentowania)/(12*(1-(12/(12 + $this->form->wysokosc_oprocentowania))^$this->form->liczba_lat));
						], [ 
							"recordID" => $this->form->id
						]);
						getMessages()->addInfo('Wykonano obliczenia.');
					}
				}
			} catch (PDOException $e){
				getMessages()->addError('Wystąpił nieoczekiwany błąd podczas zapisu rekordu');
				if (getConf()->debug) getMessages()->addError($e->getMessage());			
			}
				
		}else{
			// Gdy błąd walidacji to pozostań na stronie
			$this->generateView();
		}
	}
	
	public function action_calcShow(){
		getMessages()->addInfo('Witaj w kalkulatorze kredytowym');
		$this->generateView();
	}
	
	/**
	 * Wygenerowanie widoku
	 */
	public function generateView(){

		getSmarty()->assign('user',unserialize($_SESSION['user']));
				
		getSmarty()->assign('page_title','Kalkulator kredytowy - role');

		getSmarty()->assign('form',$this->form);
		getSmarty()->assign('res',$this->result);
		
		getSmarty()->display('CalcView.tpl');
	}
}
