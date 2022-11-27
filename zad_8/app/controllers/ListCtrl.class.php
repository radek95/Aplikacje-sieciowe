<?php

namespace app\controllers;

use app\forms\PersonSearchForm;
use PDOException;

class ListCtrl {

	private $form; //dane formularza wyszukiwania
	private $records; //rekordy pobrane z bazy danych

	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new SearchForm();
	}
		
	public function validate() {
		// 1. sprawdzenie, czy parametry zostały przekazane
		$this->form->kwota = getFromRequest('kwota',true,'Błędne wywołanie aplikacji');
		$this->form->liczba_lat = getFromRequest('liczba lat',true,'Błędne wywołanie aplikacji');
		$this->form->wysokosc_oprocentowania = getFromRequest('wysokosc oprocentowania',true,'Błędne wywołanie aplikacji');
		
		return ! getMessages()->isError();
	}
	
	public function action_personList(){
		// 1. Walidacja danych formularza (z pobraniem)
		
		$this->getParams();
		$this->validate();
		
		// 2. Przygotowanie mapy z parametrami wyszukiwania 
		$search_params = []; //przygotowanie pustej struktury 
		if ( isset($this->form->recordID) && strlen($this->form->recordID) > 0) {
			$search_params['recordID[~]'] = $this->form->recordID.'%';
		}
		
		// 3. Pobranie listy rekordów z bazy danych
		
		//przygotowanie frazy where na wypadek większej liczby parametrów
		$num_params = sizeof($search_params);
		if ($num_params > 1) {
			$where = [ "AND" => &$search_params ];
		} else {
			$where = &$search_params;
		}
		//dodanie frazy sortującej po ID
		$where ["ORDER"] = "recordID";
		//wykonanie zapytania
		
		try{
			$this->records = getDB()->select("person", [
					"recordID",
					"kwota",
					"liczba lat",
					"wysokosc oprocentowania",
				], $where );
		} catch (PDOException $e){
			getMessages()->addError('Wystąpił błąd podczas pobierania rekordów');
			if (getConf()->debug) getMessages()->addError($e->getMessage());			
		}	
		
		// 4. wygeneruj widok
		getSmarty()->assign('searchForm',$this->form); // dane formularza (wyszukiwania w tym wypadku)
		getSmarty()->assign('people',$this->records);  // lista rekordów z bazy danych
		getSmarty()->display('PersonList.tpl');
	}
	
}
