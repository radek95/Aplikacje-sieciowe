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
use app\forms\CalcForm;
use app\transfer\CalcResult;

class CalcCtrl {

	private $form;   //dane formularza (do obliczeń i dla widoku)
	private $wysokosc_raty; //inne dane dla widoku

	/** 
	 * Konstruktor - inicjalizacja właściwości
	 */
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new CalcForm();
		$this->wysokosc_raty = new CalcResult();
	}
	
	/** 
	 * Pobranie parametrów
	 */
	public function getParams(){
		$this->form->kwota = getFromRequest('kwota');
		$this->form->liczba_lat = getFromRequest('liczba lat');
		$this->form->wysokosc_oprocentowania = getFromRequest('wysokosc oprocentowania');
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
		if ($this->form->kwota == "") {
			$this->msgs->addError('Nie podano kwoty');
		}
		if ($this->form->liczba_lat == "") {
			$this->msgs->addError('Nie podano liczby lat');
		}
		if ($this->form->wysokosc_oprocentowania == "") {
			$this->msgs->addError('Nie podano wysokosci oprocentowania');
		}
		
		// nie ma sensu walidować dalej gdy brak parametrów
		if (! getMessages()->isError()) {
			
			// sprawdzenie, czy $x i $y są liczbami całkowitymi
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
	public function process(){

		$this->getParams();
		
		if ($this->validate()) {
				
			//konwersja parametrów na int
			$this->form->kwota = intval($this->form->kwota);
			$this->form->liczba_lat = intval($this->form->liczba_lat);
			$this->form->wysokosc_oprocentowania = doubleval($this->form->wysokosc_oprocentowania);
			$this->msgs->addInfo('Parametry sa poprawne.');
				
			//wykonanie operacji
			$this->result->result = ($this->form->kwota * $this->form->wysokosc_oprocentowania)/(12*(1-(12/(12 + $this->form->wysokosc_oprocentowania))^$this->form->liczba_lat));
			getMessages()->addInfo('Wykonano obliczenia.');
		}	
		$this->generateView();
	}
	
	
	/**
	 * Wygenerowanie widoku
	 */
	public function generateView(){
		//nie trzeba już tworzyć Smarty i przekazywać mu konfiguracji i messages
		// - wszystko załatwia funkcja getSmarty()
		
		getSmarty()->assign('page_title','Zad6a&6b');
		getSmarty()->assign('page_description','Kontroler główny, nowa struktura i autoloader.');
		getSmarty()->assign('page_header','Kontroler główny, nowa struktura i autoloader.');
					
		getSmarty()->assign('form',$this->form);
		getSmarty()->assign('res',$this->wysokosc_raty);
		
		getSmarty()->display('CalcView.tpl'); // już nie podajemy pełnej ścieżki - foldery widoków są zdefiniowane przy ładowaniu Smarty
	}
}
